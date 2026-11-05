<?php

namespace App\Http\Livewire\ManualTracking\Allocation;

use App\Http\Livewire\Concerns\AuthorizesPermissions;
use App\Models\ManualTracking\ManualAllocation;
use App\Models\ManualTracking\ManualAllocationImport;
use App\Models\ManualTracking\ManualWoredaAllocation;
use App\Models\Region;
use App\Services\ManualTracking\AllocationImportService;
use App\Services\ManualTracking\WoredaAllocationImportService;
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
    public $woredaId = '';
    public $search = '';
    public $language = '';
    public $grade = '';
    public $subject = '';
    // '' | complete | in_progress | not_started, against the copies received
    public $progress = '';
    public $recordes = 25;

    // Upload: the workbook, the year it is for, and the checked result shown before saving
    public $workbook;
    public $importYear = '';
    public $preview = null;
    // 'region': the Ministry workbook; 'woreda': a regional bureau's split of its zones between woredas
    public $importLevel = 'region';
    public $importRegion = 'Oromia';
    public $woredaPreview = null;

    protected $queryString = [
        'year' => ['except' => ''], 'regionId' => ['except' => ''], 'zoneId' => ['except' => ''], 'woredaId' => ['except' => ''], 'search' => ['except' => ''],
        'language' => ['except' => ''], 'grade' => ['except' => ''], 'subject' => ['except' => ''], 'progress' => ['except' => ''],
    ];

    public const PROGRESS = [
        'complete' => 'Fully received',
        'in_progress' => 'In progress',
        'not_started' => 'Not started',
    ];

    public function mount(): void
    {
        $this->year = $this->year ?: (string) (ManualAllocation::latestYear() ?? AcademicYear::current());
        $this->importYear = (string) AcademicYear::current();
        $this->applyLocks();
    }

    public function updating($property): void
    {
        if (in_array($property, ['year', 'regionId', 'zoneId', 'woredaId', 'search', 'language', 'grade', 'subject', 'progress', 'recordes'], true)) {
            $this->resetPage();
        }
    }

    public function updatedRegionId(): void
    {
        $this->zoneId = '';
        $this->woredaId = '';
        $this->applyLocks();
    }

    public function updatedZoneId(): void
    {
        $this->woredaId = '';
        $this->applyLocks();
    }

    public function updatedWorkbook(): void
    {
        $this->preview = null;
        $this->woredaPreview = null;
    }

    public function updatedImportLevel(): void
    {
        $this->preview = null;
        $this->woredaPreview = null;
    }

    public function checkWorkbook(AllocationImportService $importer, WoredaAllocationImportService $woredaImporter): void
    {
        $this->validateUpload();

        if ($this->importLevel === 'woreda') {
            $this->woredaPreview = $this->summariseWoreda($woredaImporter->parse($this->workbook->getRealPath(), (string) $this->importRegion, (string) $this->importYear));

            return;
        }

        $result = $importer->parse($this->workbook->getRealPath(), (string) $this->importYear);
        $this->preview = $this->summarise($result);
    }

    public function importWorkbook(AllocationImportService $importer, WoredaAllocationImportService $woredaImporter)
    {
        $this->validateUpload();

        if ($this->importLevel === 'woreda') {
            $result = $woredaImporter->commit(Auth::user(), $this->workbook->getRealPath(), $this->workbook->getClientOriginalName(), (string) $this->importRegion, (string) $this->importYear);
            $this->woredaPreview = $this->summariseWoreda($result);

            if ($result['errors']) {
                return $this->alert('error', 'Nothing was imported. Fix the problems listed and upload again.');
            }

            $this->reset('workbook', 'woredaPreview');
            $this->year = (string) $this->importYear;
            $this->regionId = (string) $result['region_id'];
            $this->resetPage();

            return $this->alert('success', sprintf('Woreda allocation for %s saved: %s woredas, %s copies.',
                $result['region'], number_format($result['woreda_count']), number_format($result['total'])));
        }

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

    public function filterProgress(string $progress): void
    {
        $this->progress = $this->progress === $progress || ! isset(self::PROGRESS[$progress]) ? '' : $progress;
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset('search', 'language', 'grade', 'subject', 'progress');
        $this->resetPage();
    }

    public function cancelImport(): void
    {
        $this->reset('workbook', 'preview', 'woredaPreview');
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

        // The zone's split between woredas, as the regional bureau gave it
        $woredaRows = collect();
        $woredaTitles = collect();
        $woredaProgress = collect();
        $woredaBase = fn () => ManualWoredaAllocation::query()
            ->accessibleBy($actor)
            ->where('manual_woreda_allocations.academic_year', $this->year)
            ->where('manual_woreda_allocations.zone_id', $zoneId);

        if ($zoneId !== '') {
            $woredaRows = $woredaBase()
                ->join('woredas', 'woredas.id', '=', 'manual_woreda_allocations.woreda_id')
                ->groupBy('manual_woreda_allocations.woreda_id', 'woredas.name')
                ->selectRaw('manual_woreda_allocations.woreda_id, woredas.name, COUNT(DISTINCT manual_book_id) as titles')
                ->selectRaw("SUM(CASE WHEN edition = 'student' THEN quantity ELSE 0 END) as student")
                ->selectRaw("SUM(CASE WHEN edition = 'teacher' THEN quantity ELSE 0 END) as teacher")
                ->orderBy('woredas.name')
                ->get();

            if ($woredaRows->isNotEmpty()) {
                $progress = $this->progressByPlace(ManualAllocation::inboundWoredaProgress(
                    $woredaRows->pluck('woreda_id')->all(),
                    $woredaBase()->distinct()->pluck('manual_book_id')->all()
                ), 'woreda_id');
                $woredaRows->each(fn ($row) => $row->setAttribute('progress', $progress[$row->woreda_id] ?? null));
            }
        }

        $woredaId = $woredaRows->contains('woreda_id', (int) $this->woredaId) ? (string) $this->woredaId : '';
        if ($woredaId !== '') {
            $woredaTitles = $woredaBase()
                ->where('manual_woreda_allocations.woreda_id', $woredaId)
                ->join('manual_books', 'manual_books.id', '=', 'manual_woreda_allocations.manual_book_id')
                ->when(trim($this->search) !== '', fn ($query) => $query->where(fn ($match) => $match
                    ->where('manual_books.title', 'like', '%' . trim($this->search) . '%')
                    ->orWhere('manual_books.language', 'like', '%' . trim($this->search) . '%')))
                ->when($this->language !== '', fn ($query) => $query->where('manual_books.language', $this->language))
                ->when($this->grade !== '', fn ($query) => $query->where('manual_books.grade_name', $this->grade))
                ->when($this->subject !== '', fn ($query) => $query->where('manual_books.subject_name', $this->subject))
                ->groupBy('manual_woreda_allocations.manual_book_id', 'manual_books.title', 'manual_books.language', 'manual_books.grade_name', 'manual_books.subject_name')
                ->select('manual_woreda_allocations.manual_book_id', 'manual_books.title', 'manual_books.language', 'manual_books.grade_name', 'manual_books.subject_name')
                ->selectRaw("SUM(CASE WHEN manual_woreda_allocations.edition = 'student' THEN manual_woreda_allocations.quantity ELSE 0 END) as student")
                ->selectRaw("SUM(CASE WHEN manual_woreda_allocations.edition = 'teacher' THEN manual_woreda_allocations.quantity ELSE 0 END) as teacher")
                ->orderByRaw('MIN(manual_woreda_allocations.source_sheet)')
                ->orderBy('manual_books.grade_name')
                ->get();
            $woredaProgress = ManualAllocation::inboundWoredaProgress([(int) $woredaId], $woredaTitles->pluck('manual_book_id')->all());
        }

        // Titles of the chosen zone, or the region rows; the title filters narrow both the table and the figures above it
        $titleBase = fn () => $base()
            ->when($zoneId !== '',
                fn ($query) => $query->where('manual_allocations.zone_id', $zoneId),
                fn ($query) => $query->whereNull('manual_allocations.zone_id'))
            ->join('manual_books', 'manual_books.id', '=', 'manual_allocations.manual_book_id')
            ->when(trim($this->search) !== '', fn ($query) => $query->where(fn ($match) => $match
                ->where('manual_books.title', 'like', '%' . trim($this->search) . '%')
                ->orWhere('manual_books.language', 'like', '%' . trim($this->search) . '%')
                ->orWhere('manual_allocations.source_title', 'like', '%' . trim($this->search) . '%')))
            ->when($this->language !== '', fn ($query) => $query->where('manual_books.language', $this->language))
            ->when($this->grade !== '', fn ($query) => $query->where('manual_books.grade_name', $this->grade))
            ->when($this->subject !== '', fn ($query) => $query->where('manual_books.subject_name', $this->subject));

        // Every matching region and title with its progress, for the figures and the progress filter
        $pairs = $titleBase()
            ->groupBy('manual_allocations.region_id', 'manual_allocations.manual_book_id')
            ->select('manual_allocations.region_id', 'manual_allocations.manual_book_id')
            ->selectRaw("SUM(CASE WHEN manual_allocations.edition = 'student' THEN manual_allocations.quantity ELSE 0 END) as student")
            ->selectRaw("SUM(CASE WHEN manual_allocations.edition = 'teacher' THEN manual_allocations.quantity ELSE 0 END) as teacher")
            ->get();
        $pairProgress = $zoneId !== ''
            ? ManualAllocation::inboundZoneProgress([(int) $zoneId], $pairs->pluck('manual_book_id')->unique()->values()->all())
            : ManualAllocation::inboundProgress($pairs->pluck('region_id')->unique()->values()->all(), $pairs->pluck('manual_book_id')->unique()->values()->all());
        $pairs->each(function ($pair) use ($pairProgress, $zoneId) {
            $progress = $pairProgress[($zoneId !== '' ? $zoneId : $pair->region_id) . ':' . $pair->manual_book_id] ?? null;
            $pair->setAttribute('progress', self::progressOf($pair, $progress));
            $pair->setAttribute('sent_student', (int) ($progress->sent_student ?? 0));
            $pair->setAttribute('sent_teacher', (int) ($progress->sent_teacher ?? 0));
            $pair->setAttribute('received_student', (int) ($progress->received_student ?? 0));
            $pair->setAttribute('received_teacher', (int) ($progress->received_teacher ?? 0));
        });
        $stats = $this->stats($pairs);

        $titleQuery = $titleBase()
            ->join('regions', 'regions.id', '=', 'manual_allocations.region_id')
            ->when($this->progress !== '', function ($query) use ($pairs) {
                $matching = $pairs->where('progress', $this->progress)->groupBy('region_id');

                $query->where(fn ($match) => $matching->isEmpty()
                    ? $match->whereRaw('1 = 0')
                    : $matching->each(fn ($rows, $regionId) => $match->orWhere(fn ($region) => $region
                        ->where('manual_allocations.region_id', $regionId)
                        ->whereIn('manual_allocations.manual_book_id', $rows->pluck('manual_book_id')->all()))));
            })
            ->groupBy('manual_allocations.region_id', 'regions.name', 'manual_allocations.manual_book_id', 'manual_books.title', 'manual_books.language', 'manual_books.grade_name', 'manual_books.subject_name')
            ->select('manual_allocations.region_id', 'manual_allocations.manual_book_id', 'manual_books.title', 'manual_books.language', 'manual_books.grade_name', 'manual_books.subject_name')
            ->selectRaw("SUM(CASE WHEN manual_allocations.edition = 'student' THEN manual_allocations.quantity ELSE 0 END) as student")
            ->selectRaw("SUM(CASE WHEN manual_allocations.edition = 'teacher' THEN manual_allocations.quantity ELSE 0 END) as teacher")
            ->selectRaw("GROUP_CONCAT(DISTINCT manual_allocations.source_title SEPARATOR ' · ') as source_titles")
            ->selectRaw("MAX(CASE WHEN manual_allocations.edition = 'teacher' THEN manual_allocations.source_title END) as teacher_title")
            ->orderBy('regions.name')
            // In the order the workbook lists them
            ->orderByRaw('MIN(manual_allocations.source_row)')
            ->orderBy('manual_books.title');

        $titles = $titleQuery->paginate($this->recordes);
        $prefix = $zoneId !== '' ? fn ($title) => $zoneId : fn ($title) => $title->region_id;
        $titles->getCollection()->each(fn ($title) => $title->setAttribute('progress_key', $prefix($title) . ':' . $title->manual_book_id));
        $titleProgress = $pairProgress;

        // Filter choices: what the year's allocation in the chosen place actually lists
        $options = fn (string $column) => $base()
            ->when($zoneId !== '', fn ($query) => $query->where('manual_allocations.zone_id', $zoneId), fn ($query) => $query->whereNull('manual_allocations.zone_id'))
            ->join('manual_books', 'manual_books.id', '=', 'manual_allocations.manual_book_id')
            ->whereNotNull("manual_books.{$column}")->where("manual_books.{$column}", '!=', '')
            ->distinct()->orderBy("manual_books.{$column}")->pluck("manual_books.{$column}");

        $lastImport = ManualAllocationImport::query()->where('academic_year', $this->year)->where('level', 'region')->latest('id')->first();
        $lastWoredaImport = $woredaRows->isNotEmpty()
            ? ManualAllocationImport::query()->where('academic_year', $this->year)->where('level', 'woreda')->latest('id')->first()
            : null;
        $awaiting = $actor->hasNationalAccess() || $actor->hasRole('Admin') ? $this->awaiting() : [];

        return view('livewire.manual-tracking.allocation.allocation-index', [
            'regions' => $regions,
            'regionLocked' => $location->isLocked('region_id'),
            'zoneLocked' => $location->isLocked('zone_id'),
            'zoneRows' => $zoneRows,
            'titleZone' => $zoneId !== '' ? $zoneRows->firstWhere('zone_id', (int) $zoneId) : null,
            'woredaLocked' => $location->isLocked('woreda_id'),
            'woredaRows' => $woredaRows,
            'titleWoreda' => $woredaId !== '' ? $woredaRows->firstWhere('woreda_id', (int) $woredaId) : null,
            'woredaTitles' => $woredaTitles,
            'woredaProgress' => $woredaProgress,
            'lastWoredaImport' => $lastWoredaImport,
            'importRegions' => Region::query()->orderBy('name')->pluck('name'),
            'years' => $years,
            'regionRows' => $regionRows->map(fn ($row) => $row->setAttribute('name', $regionNames[$row->region_id] ?? '—')
                ->setAttribute('progress', $regionProgress[$row->region_id] ?? null))->sortBy('name')->values(),
            'titles' => $titles,
            'titleProgress' => $titleProgress,
            'regionNames' => $regionNames,
            'lastImport' => $lastImport,
            'awaiting' => $awaiting,
            'canImport' => $actor->can('manual-tracking.allocations.import'),
            'stats' => $stats,
            'languages' => $options('language'),
            'grades' => $options('grade_name'),
            'subjects' => $options('subject_name'),
            // The figures follow every filter except progress, whose counts they show
            'statsFiltered' => trim($this->search) !== '' || $this->language !== '' || $this->grade !== '' || $this->subject !== '',
            'filtersActive' => trim($this->search) !== '' || $this->language !== '' || $this->grade !== '' || $this->subject !== '' || $this->progress !== '',
        ])->extends('main.manual-tracking.index');
    }

    // Locked levels of the user's area win over whatever the filters say
    private function applyLocks(): void
    {
        $locked = ScopedLocation::for(Auth::user())->locked();
        $this->regionId = $locked['region_id'] ?? $this->regionId;
        $this->zoneId = $locked['zone_id'] ?? $this->zoneId;
        $this->woredaId = $locked['woreda_id'] ?? $this->woredaId;
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
     * Where a title stands in a place: every allocated copy received, some sent or received, or nothing yet.
     */
    public static function progressOf($allocation, $inbound): string
    {
        $allocated = (int) $allocation->student + (int) $allocation->teacher;
        $received = (int) ($inbound->received_student ?? 0) + (int) ($inbound->received_teacher ?? 0);
        $sent = (int) ($inbound->sent_student ?? 0) + (int) ($inbound->sent_teacher ?? 0);

        if ($allocated > 0 && $received >= $allocated
            && (int) ($inbound->received_student ?? 0) >= (int) $allocation->student
            && (int) ($inbound->received_teacher ?? 0) >= (int) $allocation->teacher) {
            return 'complete';
        }

        return $sent + $received > 0 ? 'in_progress' : 'not_started';
    }

    /**
     * Totals over the matching regions and titles. Copies due are counted per title, so an
     * over-delivered title does not hide another one still short.
     */
    private function stats($pairs): array
    {
        $sum = fn (string $key) => (int) $pairs->sum($key);
        $student = $sum('student');
        $teacher = $sum('teacher');
        $receivedStudent = (int) $pairs->sum(fn ($pair) => min($pair->student, $pair->received_student));
        $receivedTeacher = (int) $pairs->sum(fn ($pair) => min($pair->teacher, $pair->received_teacher));
        $percent = fn (int $part, int $whole) => $whole > 0 ? round($part / $whole * 100, 1) : 0;

        return [
            'titles' => $pairs->pluck('manual_book_id')->unique()->count(),
            'lines' => $pairs->count(),
            'regions' => $pairs->pluck('region_id')->unique()->count(),
            'student' => $student,
            'teacher' => $teacher,
            'total' => $student + $teacher,
            'sent' => $sum('sent_student') + $sum('sent_teacher'),
            'received_student' => $receivedStudent,
            'received_teacher' => $receivedTeacher,
            'received' => $receivedStudent + $receivedTeacher,
            'due_student' => $student - $receivedStudent,
            'due_teacher' => $teacher - $receivedTeacher,
            'student_percent' => $percent($receivedStudent, $student),
            'teacher_percent' => $percent($receivedTeacher, $teacher),
            'percent' => $percent($receivedStudent + $receivedTeacher, $student + $teacher),
            'progress' => collect(self::PROGRESS)->map(fn ($label, $key) => $pairs->where('progress', $key)->count())->all(),
        ];
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
            'importLevel' => 'required|in:region,woreda',
            'importRegion' => 'required_if:importLevel,woreda|nullable|string|exists:regions,name',
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

    private function summariseWoreda(array $result): array
    {
        return [
            'region' => $result['region'],
            'sheets' => $result['sheets'],
            'zones' => $result['zones'],
            'woreda_count' => $result['woreda_count'],
            'new_woredas' => $result['new_woredas'],
            'line_count' => $result['line_count'],
            'title_count' => $result['title_count'],
            'total' => $result['total'],
            'warnings' => array_slice($result['warnings'], 0, 200),
            'errors' => $result['errors'],
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
