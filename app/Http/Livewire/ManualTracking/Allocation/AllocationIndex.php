<?php

namespace App\Http\Livewire\ManualTracking\Allocation;

use App\Http\Livewire\Concerns\AuthorizesPermissions;
use App\Models\ManualTracking\ManualAllocation;
use App\Models\ManualTracking\ManualAllocationImport;
use App\Models\Region;
use App\Services\ManualTracking\AllocationImportService;
use App\Support\AcademicYear;
use App\Support\ScopedLocation;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

/**
 * The Ministry's allocation per region and title, set against what has been sent into the
 * region and received there. National users can upload a new allocation workbook: it is
 * checked first and only saved after the preview.
 */
class AllocationIndex extends Component
{
    use WithPagination;
    use WithFileUploads;
    use AuthorizesPermissions;

    public $year = '';
    public $regionId = '';
    public $zoneId = '';
    public $search = '';
    public $unconfirmedOnly = false;
    public $recordes = 25;

    // Upload: the workbook, the year it is for, and the checked result shown before saving
    public $workbook;
    public $importYear = '';
    public $preview = null;

    protected $queryString = ['year' => ['except' => ''], 'regionId' => ['except' => ''], 'zoneId' => ['except' => ''], 'search' => ['except' => '']];

    public function mount(): void
    {
        $this->year = $this->year ?: (string) (ManualAllocation::latestYear() ?? AcademicYear::current());
        $this->importYear = (string) AcademicYear::current();
        $this->applyLocks();
    }

    public function updating($property): void
    {
        if (in_array($property, ['year', 'regionId', 'zoneId', 'search', 'unconfirmedOnly', 'recordes'], true)) {
            $this->resetPage();
        }
    }

    public function updatedRegionId(): void
    {
        $this->zoneId = '';
        $this->applyLocks();
    }

    public function updatedZoneId(): void
    {
        $this->applyLocks();
    }

    public function updatedWorkbook(): void
    {
        $this->preview = null;
    }

    public function checkWorkbook(AllocationImportService $importer): void
    {
        $this->validateUpload();

        $result = $importer->parse($this->workbook->getRealPath(), (string) $this->importYear);
        $this->preview = $this->summarise($result);
    }

    public function importWorkbook(AllocationImportService $importer)
    {
        $this->validateUpload();

        $result = $importer->commit(Auth::user(), $this->workbook->getRealPath(), $this->workbook->getClientOriginalName(), (string) $this->importYear);
        $this->preview = $this->summarise($result);

        if ($result['errors']) {
            return $this->alert('error', 'Nothing was imported. Fix the rows listed and upload again.');
        }

        $this->reset('workbook', 'preview');
        $this->year = (string) $this->importYear;
        $this->resetPage();
        $this->alert('success', sprintf('Allocation for %s saved: %s titles, %s copies.',
            AcademicYear::label((int) $this->importYear), number_format(count($result['titles'])), number_format($result['total'])));
    }

    public function cancelImport(): void
    {
        $this->reset('workbook', 'preview');
        $this->resetValidation();
    }

    public function render()
    {
        $actor = Auth::user();
        $location = ScopedLocation::for($actor);
        $regions = $location->regions();
        $years = ManualAllocation::query()->distinct()->orderByDesc('academic_year')->pluck('academic_year');

        $base = fn () => ManualAllocation::query()
            ->accessibleBy($actor)
            ->where('manual_allocations.academic_year', $this->year)
            ->when($this->regionId !== '', fn ($query) => $query->where('manual_allocations.region_id', $this->regionId));

        // Region totals come from the rows without a zone, so zone shares are never added on top
        $regionRows = $base()
            ->whereNull('zone_id')
            ->groupBy('region_id')
            ->selectRaw('region_id, COUNT(DISTINCT manual_book_id) as titles')
            ->selectRaw("SUM(CASE WHEN edition = 'student' THEN quantity ELSE 0 END) as student")
            ->selectRaw("SUM(CASE WHEN edition = 'teacher' THEN quantity ELSE 0 END) as teacher")
            ->selectRaw('SUM(CASE WHEN type_confirmed THEN 0 ELSE 1 END) as unconfirmed')
            ->get();

        $regionIds = $regionRows->pluck('region_id')->all();
        $regionBooks = $base()->whereNull('zone_id')->distinct()->pluck('manual_book_id')->all();
        $regionProgress = $this->progressByPlace(ManualAllocation::inboundProgress($regionIds, $regionBooks), 'region_id');
        $regionNames = $regions->pluck('name', 'id');

        $zoneRows = collect();
        if ($this->regionId !== '') {
            $zoneRows = $base()
                ->whereNotNull('manual_allocations.zone_id')
                ->join('zones', 'zones.id', '=', 'manual_allocations.zone_id')
                ->groupBy('manual_allocations.zone_id', 'zones.name')
                ->selectRaw('manual_allocations.zone_id, zones.name, COUNT(DISTINCT manual_book_id) as titles')
                ->selectRaw("SUM(CASE WHEN edition = 'student' THEN quantity ELSE 0 END) as student")
                ->selectRaw("SUM(CASE WHEN edition = 'teacher' THEN quantity ELSE 0 END) as teacher")
                ->orderBy('zones.name')
                ->get();
            $zoneProgress = $this->progressByPlace(ManualAllocation::inboundZoneProgress(
                $zoneRows->pluck('zone_id')->all(),
                $base()->whereNotNull('zone_id')->distinct()->pluck('manual_book_id')->all()
            ), 'zone_id');
            $zoneRows->each(fn ($row) => $row->setAttribute('progress', $zoneProgress[$row->zone_id] ?? null));
        }

        // A zone without its own split (or one not in this region) shows the region's titles
        $zoneId = $zoneRows->contains('zone_id', (int) $this->zoneId) ? (string) $this->zoneId : '';

        $titleQuery = $base()
            ->when($zoneId !== '',
                fn ($query) => $query->where('manual_allocations.zone_id', $zoneId),
                fn ($query) => $query->whereNull('manual_allocations.zone_id'))
            ->join('manual_books', 'manual_books.id', '=', 'manual_allocations.manual_book_id')
            ->join('regions', 'regions.id', '=', 'manual_allocations.region_id')
            ->when(trim($this->search) !== '', fn ($query) => $query->where(fn ($match) => $match
                ->where('manual_books.title', 'like', '%' . trim($this->search) . '%')
                ->orWhere('manual_books.language', 'like', '%' . trim($this->search) . '%')
                ->orWhere('manual_allocations.source_title', 'like', '%' . trim($this->search) . '%')))
            ->when($this->unconfirmedOnly, fn ($query) => $query->whereIn('manual_allocations.manual_book_id', ManualAllocation::query()
                ->where('academic_year', $this->year)->where('type_confirmed', false)->select('manual_book_id')))
            ->groupBy('manual_allocations.region_id', 'regions.name', 'manual_allocations.manual_book_id', 'manual_books.title', 'manual_books.language', 'manual_books.grade_name')
            ->select('manual_allocations.region_id', 'manual_allocations.manual_book_id', 'manual_books.title', 'manual_books.language', 'manual_books.grade_name')
            ->selectRaw("SUM(CASE WHEN manual_allocations.edition = 'student' THEN manual_allocations.quantity ELSE 0 END) as student")
            ->selectRaw("SUM(CASE WHEN manual_allocations.edition = 'teacher' THEN manual_allocations.quantity ELSE 0 END) as teacher")
            ->selectRaw('MIN(manual_allocations.type_confirmed) as type_confirmed')
            ->selectRaw("GROUP_CONCAT(DISTINCT manual_allocations.source_title SEPARATOR ' · ') as source_titles")
            ->selectRaw("MAX(CASE WHEN manual_allocations.edition = 'teacher' THEN manual_allocations.source_title END) as teacher_title")
            ->orderBy('regions.name')
            // In the order the workbook lists them
            ->orderByRaw('MIN(manual_allocations.source_row)')
            ->orderBy('manual_books.title');

        $titles = $titleQuery->paginate($this->recordes);
        $pageBooks = collect($titles->items())->pluck('manual_book_id')->unique()->values()->all();

        if ($zoneId !== '') {
            $titleProgress = ManualAllocation::inboundZoneProgress([(int) $zoneId], $pageBooks);
            $titles->getCollection()->each(fn ($title) => $title->setAttribute('progress_key', $zoneId . ':' . $title->manual_book_id));
        } else {
            $titleProgress = ManualAllocation::inboundProgress(collect($titles->items())->pluck('region_id')->unique()->values()->all(), $pageBooks);
            $titles->getCollection()->each(fn ($title) => $title->setAttribute('progress_key', $title->region_id . ':' . $title->manual_book_id));
        }

        $lastImport = ManualAllocationImport::query()->where('academic_year', $this->year)->latest('id')->first();
        $awaiting = $actor->hasNationalAccess() || $actor->hasRole('Admin') ? $this->awaiting() : [];

        return view('livewire.manual-tracking.allocation.allocation-index', [
            'regions' => $regions,
            'regionLocked' => $location->isLocked('region_id'),
            'zoneLocked' => $location->isLocked('zone_id'),
            'zoneRows' => $zoneRows,
            'titleZone' => $zoneId !== '' ? $zoneRows->firstWhere('zone_id', (int) $zoneId) : null,
            'years' => $years,
            'regionRows' => $regionRows->map(fn ($row) => $row->setAttribute('name', $regionNames[$row->region_id] ?? '—')
                ->setAttribute('progress', $regionProgress[$row->region_id] ?? null))->sortBy('name')->values(),
            'titles' => $titles,
            'titleProgress' => $titleProgress,
            'regionNames' => $regionNames,
            'lastImport' => $lastImport,
            'awaiting' => $awaiting,
            'canImport' => $actor->can('manual-tracking.allocations.import'),
        ])->extends('main.manual-tracking.index');
    }

    // Locked levels of the user's area win over whatever the filters say
    private function applyLocks(): void
    {
        $locked = ScopedLocation::for(Auth::user())->locked();
        $this->regionId = $locked['region_id'] ?? $this->regionId;
        $this->zoneId = $locked['zone_id'] ?? $this->zoneId;
    }

    /**
     * Regions some import of the year listed in its Summary that still have no allocation.
     *
     * @return array<string, array{lines: int, total: int}>
     */
    private function awaiting(): array
    {
        $allocated = Region::query()->whereIn('id', ManualAllocation::query()->where('academic_year', $this->year)->select('region_id'))->pluck('name')->all();

        return ManualAllocationImport::query()->where('academic_year', $this->year)->orderBy('id')->get(['region_totals'])
            ->flatMap(fn (ManualAllocationImport $import) => $import->region_totals['awaiting'] ?? [])
            ->except($allocated)
            ->all();
    }

    /**
     * Inbound copies per region or zone, summed over the titles asked for.
     */
    private function progressByPlace($inbound, string $key): array
    {
        return $inbound
            ->groupBy($key)
            ->map(fn ($rows) => (object) [
                'sent' => $rows->sum('sent_student') + $rows->sum('sent_teacher'),
                'received' => $rows->sum('received_student') + $rows->sum('received_teacher'),
            ])
            ->all();
    }

    private function validateUpload(): void
    {
        $this->validate([
            'workbook' => 'required|file|mimes:xlsx|max:20480',
            'importYear' => 'required|integer|min:2000|max:2100',
        ], [], ['workbook' => 'allocation workbook', 'importYear' => 'academic year']);
    }

    // Keeps the preview small enough to travel with every Livewire request
    private function summarise(array $result): array
    {
        return [
            'regions' => $result['regions'],
            'awaiting' => $result['awaiting'],
            'zones' => $result['zones'],
            'new_zones' => $result['new_zones'],
            'line_count' => $result['line_count'],
            'title_count' => count($result['titles']),
            'total' => $result['total'],
            'warnings' => $result['warnings'],
            'errors' => array_slice($result['errors'], 0, 200),
            'error_count' => count($result['errors']),
        ];
    }

    private function alert(string $type, string $message): void
    {
        $this->dispatchBrowserEvent('alert', ['type' => $type, 'message' => $message]);
    }

    protected function permissionMap(): array
    {
        return [
            'view' => 'manual-tracking.view',
            'actions' => [
                'checkWorkbook' => 'manual-tracking.allocations.import',
                'importWorkbook' => 'manual-tracking.allocations.import',
                'cancelImport' => 'manual-tracking.allocations.import',
            ],
        ];
    }
}
