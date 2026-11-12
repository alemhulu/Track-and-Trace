<?php

namespace App\Http\Livewire\ManualTracking\Distribution;

use App\Http\Livewire\Concerns\AuthorizesPermissions;
use App\Models\ManualTracking\ManualBook;
use App\Models\ManualTracking\ManualDistribution;
use App\Models\ManualTracking\ManualDistributionLine;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use App\Support\ScopedLocation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Shipments list. Shipments are never deleted: they have moved stock, so mistakes are
 * corrected through discrepancies rather than by removing the record.
 */
class ListManualDistribution extends Component
{
    use WithPagination;
    use AuthorizesPermissions;

    public $recordes = 10;
    public $statusFilter = '';
    public $search = '';
    public $bookFilter = '';
    public $dateFrom = '';
    public $dateTo = '';
    // '' | outgoing | incoming, relative to the place chosen
    public $direction = '';
    public $overdueOnly = false;
    public $openDiscrepanciesOnly = false;

    protected $queryString = ['statusFilter' => ['except' => ''], 'search' => ['except' => '']];

    // Shows shipments leaving or arriving at the chosen place
    public $area = ['region_id' => '', 'zone_id' => '', 'woreda_id' => ''];
    public $organizationFilter = '';

    public function mount()
    {
        $this->area = $this->location()->apply([]);
        $this->organizationFilter = $this->location()->organization($this->area, '');
    }

    public function updatedArea($value, $key)
    {
        $this->area = $this->location()->apply($this->area, $key);
        $this->updatedOrganizationFilter();
    }

    public function updatedOrganizationFilter()
    {
        $this->organizationFilter = $this->location()->organization($this->area, $this->organizationFilter);
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updating($property)
    {
        if (in_array($property, ['bookFilter', 'dateFrom', 'dateTo', 'direction', 'overdueOnly', 'openDiscrepanciesOnly'], true)) {
            $this->resetPage();
        }
    }

    public function filterStatus(string $status): void
    {
        $this->statusFilter = $this->statusFilter === $status || ! isset(ManualDistribution::STATUSES[$status]) ? '' : $status;
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset('statusFilter', 'search', 'bookFilter', 'dateFrom', 'dateTo', 'direction', 'overdueOnly', 'openDiscrepanciesOnly');
        $this->organizationFilter = $this->location()->organization($this->area, '');
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

    public function render()
    {
        $actor = Auth::user();
        if ($actor && Gate::denies('viewAny', ManualDistribution::class)) {
            abort(403);
        }

        $distributions = $this->filtered()
            ->when($this->statusFilter, fn ($query) => $query->where('status', $this->statusFilter))
            ->withSum('lines', 'quantity')
            ->withSum('lines', 'teacher_guides')
            ->withSum('lines', 'received_quantity')
            ->withSum('lines', 'received_teacher_guides')
            ->withCount(['discrepancies as open_discrepancies_count' => fn ($query) => $query->where('manual_discrepancies.status', 'open')])
            ->with(['lines.book:id,title', 'organization:id,name', 'destinationOrganization:id,name'])
            ->orderByDesc('distributed_at')
            ->orderByDesc('id')
            ->paginate($this->recordes);

        return view('livewire.manual-tracking.distribution.list-manual-distribution', [
            'distributions' => $distributions,
            'canCreate' => Gate::allows('create', ManualDistribution::class),
            'locationPicker' => $this->location()->picker($this->area, null, $this->organizationFilter),
            'stats' => $this->stats(),
            'books' => ManualBook::query()->accessibleBy($actor)->orderBy('title')->get(['id', 'title']),
            'placeChosen' => $this->filtersByPlace(),
            'filtersActive' => $this->statusFilter || trim($this->search) !== '' || $this->bookFilter || $this->dateFrom || $this->dateTo
                || $this->direction || $this->overdueOnly || $this->openDiscrepanciesOnly,
        ])->extends('main.manual-tracking.index');
    }

    /**
     * Shipments the user can see, narrowed by every filter except status, so the status
     * figures above the table stay comparable while one status is shown.
     */
    private function filtered(): Builder
    {
        $search = trim($this->search);

        return ManualDistribution::query()
            ->accessibleBy(Auth::user())
            ->when($this->filtersByPlace(), function ($query) {
                $places = $this->organizationFilter
                    ? [(int) $this->organizationFilter]
                    : $this->location()->organizations($this->area)->select('id');

                $query->where(fn ($match) => match ($this->direction) {
                    'outgoing' => $match->whereIn('organization_id', $places),
                    'incoming' => $match->whereIn('destination_organization_id', $places),
                    default => $match->whereIn('organization_id', $places)->orWhereIn('destination_organization_id', $places),
                });
            })
            ->when($search !== '', fn ($query) => $query->where(fn ($match) => $match
                ->where('reference', 'like', '%' . $search . '%')
                ->orWhere('waybill_no', 'like', '%' . $search . '%')
                ->orWhere('vehicle_no', 'like', '%' . $search . '%')
                ->orWhere('driver_name', 'like', '%' . $search . '%')
                ->orWhere('carrier', 'like', '%' . $search . '%')
                ->orWhereHas('organization', fn ($place) => $place->where('name', 'like', '%' . $search . '%'))
                ->orWhereHas('destinationOrganization', fn ($place) => $place->where('name', 'like', '%' . $search . '%'))))
            ->when($this->bookFilter, fn ($query) => $query->whereHas('lines', fn ($line) => $line->where('manual_book_id', $this->bookFilter)))
            ->when($this->validDate($this->dateFrom), fn ($query) => $query->where('distributed_at', '>=', Carbon::parse($this->dateFrom)->startOfDay()))
            ->when($this->validDate($this->dateTo), fn ($query) => $query->where('distributed_at', '<=', Carbon::parse($this->dateTo)->endOfDay()))
            ->when($this->overdueOnly, fn ($query) => $query->where('status', ManualDistribution::STATUS_DISPATCHED)
                ->whereNotNull('expected_at')->where('expected_at', '<', now()))
            ->when($this->openDiscrepanciesOnly, fn ($query) => $query->whereHas('discrepancies', fn ($discrepancy) => $discrepancy->where('manual_discrepancies.status', 'open')));
    }

    private function validDate($value): bool
    {
        return is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1;
    }

    /**
     * Counts and copies over every matching shipment.
     */
    private function stats(): array
    {
        $byStatus = $this->filtered()->toBase()->reorder()->groupBy('status')->selectRaw('status, COUNT(*) as total')->pluck('total', 'status');
        $ids = fn () => $this->filtered()->when($this->statusFilter, fn ($query) => $query->where('status', $this->statusFilter))->select('manual_distributions.id');

        $copies = ManualDistributionLine::query()->whereIn('manual_distribution_id', $ids())
            ->selectRaw('COALESCE(SUM(quantity), 0) as student, COALESCE(SUM(teacher_guides), 0) as teacher')
            ->selectRaw('COALESCE(SUM(received_quantity), 0) as received_student, COALESCE(SUM(received_teacher_guides), 0) as received_teacher')
            ->selectRaw('COALESCE(SUM(damaged_quantity), 0) + COALESCE(SUM(damaged_teacher_guides), 0) as damaged')
            ->first();

        $transit = $this->filtered()->toBase()->whereIn('status', [ManualDistribution::STATUS_RECEIVED, ManualDistribution::STATUS_RECEIVED_WITH_DISCREPANCY])
            ->whereNotNull('received_at')->whereNotNull('distributed_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, distributed_at, received_at)) as hours')->value('hours');

        return [
            'total' => (int) $byStatus->sum(),
            'status' => collect(ManualDistribution::STATUSES)->map(fn ($label, $status) => (int) ($byStatus[$status] ?? 0))->all(),
            'overdue' => $this->filtered()->where('status', ManualDistribution::STATUS_DISPATCHED)->whereNotNull('expected_at')->where('expected_at', '<', now())->count(),
            'open_discrepancies' => $this->filtered()->whereHas('discrepancies', fn ($discrepancy) => $discrepancy->where('manual_discrepancies.status', 'open'))->count(),
            'student' => (int) $copies->student,
            'teacher' => (int) $copies->teacher,
            'received_student' => (int) $copies->received_student,
            'received_teacher' => (int) $copies->received_teacher,
            'damaged' => (int) $copies->damaged,
            'transit_days' => $transit !== null ? round($transit / 24, 1) : null,
        ];
    }

    // The area the user is locked to is already applied by accessibleBy; filter only on a narrower choice
    private function filtersByPlace(): bool
    {
        $locked = $this->location()->locked();

        return collect($this->area)->filter()->diffAssoc($locked)->isNotEmpty()
            || ($this->organizationFilter && ($locked['organization_id'] ?? null) !== (string) $this->organizationFilter);
    }

    private function location(): ScopedLocation
    {
        return ScopedLocation::for(Auth::user());
    }

    protected function permissionMap(): array
    {
        return ['view' => 'manual-tracking.view'];
    }
}
