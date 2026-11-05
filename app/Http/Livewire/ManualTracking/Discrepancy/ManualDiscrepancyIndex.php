<?php

namespace App\Http\Livewire\ManualTracking\Discrepancy;

use App\Http\Livewire\Concerns\AuthorizesPermissions;
use App\Models\ManualTracking\ManualDiscrepancy;
use App\Services\ManualTracking\ManualShipmentService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use App\Services\ManualTracking\ManualTrackingException;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class ManualDiscrepancyIndex extends Component
{
    use WithPagination;
    use AuthorizesPermissions;

    public $statusFilter = ManualDiscrepancy::STATUS_OPEN;
    public $search = '';
    public $typeFilter = '';
    public $editionFilter = '';
    public $dateFrom = '';
    public $dateTo = '';
    public $recordes = 10;

    public const TYPES = [
        ManualDiscrepancy::TYPE_MISSING => 'Missing',
        ManualDiscrepancy::TYPE_DAMAGED => 'Damaged',
        ManualDiscrepancy::TYPE_EXCESS => 'Excess',
    ];

    protected $queryString = ['statusFilter' => ['except' => ManualDiscrepancy::STATUS_OPEN], 'typeFilter' => ['except' => ''], 'search' => ['except' => '']];

    // Resolve form state, keyed by discrepancy id
    public $resolutions = [];
    public $notes = [];

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingRecordes()
    {
        $this->resetPage();
    }

    public function updating($property)
    {
        if (in_array($property, ['typeFilter', 'editionFilter', 'dateFrom', 'dateTo'], true)) {
            $this->resetPage();
        }
    }

    // Tiles toggle their filter: a second click shows everything again
    public function setFilter(string $filter, string $value): void
    {
        if (in_array($filter, ['statusFilter', 'typeFilter'], true)) {
            $this->{$filter} = $this->{$filter} === $value ? '' : $value;
            $this->resetPage();
        }
    }

    public function clearFilters(): void
    {
        $this->reset('search', 'typeFilter', 'editionFilter', 'dateFrom', 'dateTo');
        $this->statusFilter = '';
        $this->resetPage();
    }

    public function render()
    {
        $discrepancies = $this->filtered()
            ->when($this->statusFilter, fn ($query) => $query->where('status', $this->statusFilter))
            ->when($this->typeFilter, fn ($query) => $query->where('type', $this->typeFilter))
            ->with([
                'line.book:id,title',
                'line.distribution:id,reference,organization_id,destination_organization_id,distributed_by,received_by',
                'line.distribution.organization:id,name',
                'line.distribution.destinationOrganization:id,name',
                'raisedBy:id,name',
                'resolvedBy:id,name',
            ])
            ->orderByDesc('id')
            ->paginate($this->recordes);

        return view('livewire.manual-tracking.discrepancy.manual-discrepancy-index', [
            'discrepancies' => $discrepancies,
            'stats' => $this->stats(),
            'filtersActive' => $this->statusFilter !== ManualDiscrepancy::STATUS_OPEN || $this->typeFilter || $this->editionFilter
                || $this->dateFrom || $this->dateTo || trim($this->search) !== '',
        ])->extends('main.manual-tracking.index');
    }

    /**
     * Discrepancies the user can see, narrowed by everything except status and type, whose
     * counts the tiles show.
     */
    private function filtered(): Builder
    {
        $search = trim($this->search);
        $date = fn ($value) => is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1;

        return ManualDiscrepancy::query()
            ->accessibleBy(Auth::user())
            ->when($search !== '', fn ($query) => $query->whereHas('line', fn ($line) => $line
                ->whereHas('distribution', fn ($shipment) => $shipment->where('reference', 'like', '%' . $search . '%')
                    ->orWhereHas('organization', fn ($place) => $place->where('name', 'like', '%' . $search . '%'))
                    ->orWhereHas('destinationOrganization', fn ($place) => $place->where('name', 'like', '%' . $search . '%')))
                ->orWhereHas('book', fn ($book) => $book->where('title', 'like', '%' . $search . '%'))))
            ->when($this->editionFilter, fn ($query) => $query->where('edition', $this->editionFilter))
            ->when($date($this->dateFrom), fn ($query) => $query->where('created_at', '>=', Carbon::parse($this->dateFrom)->startOfDay()))
            ->when($date($this->dateTo), fn ($query) => $query->where('created_at', '<=', Carbon::parse($this->dateTo)->endOfDay()));
    }

    private function stats(): array
    {
        $byStatus = $this->filtered()->when($this->typeFilter, fn ($query) => $query->where('type', $this->typeFilter))
            ->toBase()->groupBy('status')
            ->selectRaw('status, COUNT(*) as total, COALESCE(SUM(quantity), 0) as copies')
            ->get()->keyBy('status');
        $byType = $this->filtered()->when($this->statusFilter, fn ($query) => $query->where('status', $this->statusFilter))
            ->toBase()->groupBy('type')
            ->selectRaw('type, COUNT(*) as total, COALESCE(SUM(quantity), 0) as copies')
            ->get()->keyBy('type');
        $hours = $this->filtered()->when($this->typeFilter, fn ($query) => $query->where('type', $this->typeFilter))
            ->where('status', ManualDiscrepancy::STATUS_RESOLVED)->whereNotNull('resolved_at')
            ->toBase()->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, resolved_at)) as hours')->value('hours');
        $oldest = $this->filtered()->when($this->typeFilter, fn ($query) => $query->where('type', $this->typeFilter))
            ->where('status', ManualDiscrepancy::STATUS_OPEN)->min('created_at');

        return [
            'open' => (int) ($byStatus[ManualDiscrepancy::STATUS_OPEN]->total ?? 0),
            'open_copies' => (int) ($byStatus[ManualDiscrepancy::STATUS_OPEN]->copies ?? 0),
            'resolved' => (int) ($byStatus[ManualDiscrepancy::STATUS_RESOLVED]->total ?? 0),
            'resolved_copies' => (int) ($byStatus[ManualDiscrepancy::STATUS_RESOLVED]->copies ?? 0),
            'types' => collect(self::TYPES)->map(fn ($label, $type) => [
                'total' => (int) ($byType[$type]->total ?? 0),
                'copies' => (int) ($byType[$type]->copies ?? 0),
            ])->all(),
            'resolve_days' => $hours !== null ? round($hours / 24, 1) : null,
            'oldest_open_days' => $oldest ? (int) Carbon::parse($oldest)->diffInDays(now()) : null,
        ];
    }

    public function resolve($id, ManualShipmentService $shipments)
    {
        $discrepancy = ManualDiscrepancy::query()->accessibleBy(Auth::user())->findOrFail($id);

        if (empty($this->resolutions[$id])) {
            return $this->alert('error', 'Choose a resolution first.');
        }

        try {
            $shipments->resolve(Auth::user(), $discrepancy, $this->resolutions[$id], $this->notes[$id] ?? null);
        } catch (ManualTrackingException $exception) {
            return $this->alert('error', $exception->getMessage());
        }

        unset($this->resolutions[$id], $this->notes[$id]);
        $this->alert('success', 'Discrepancy resolved.');
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
                'resolve' => 'manual-tracking.discrepancies.resolve',
            ],
        ];
    }
}
