<?php

namespace App\Http\Livewire\ManualTracking\Distribution;

use App\Http\Livewire\Concerns\AuthorizesPermissions;
use App\Models\ManualTracking\ManualDistribution;
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

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $actor = Auth::user();
        if ($actor && Gate::denies('viewAny', ManualDistribution::class)) {
            abort(403);
        }

        $distributions = ManualDistribution::query()
            ->withSum('lines', 'quantity')
            ->withSum('lines', 'teacher_guides')
            ->withCount(['lines as open_discrepancies_count' => fn ($query) => $query->whereHas(
                'discrepancies',
                fn ($discrepancy) => $discrepancy->where('status', 'open')
            )])
            ->with(['lines.book:id,title', 'organization:id,name', 'destinationOrganization:id,name'])
            ->accessibleBy($actor)
            ->when($this->filtersByPlace(), function ($query) {
                $places = $this->organizationFilter
                    ? [(int) $this->organizationFilter]
                    : $this->location()->organizations($this->area)->select('id');

                $query->where(fn ($match) => $match->whereIn('organization_id', $places)->orWhereIn('destination_organization_id', $places));
            })
            ->when($this->statusFilter, fn ($query) => $query->where('status', $this->statusFilter))
            ->when($this->search, fn ($query) => $query->where(fn ($match) => $match
                ->where('reference', 'like', '%' . $this->search . '%')
                ->orWhere('waybill_no', 'like', '%' . $this->search . '%')))
            ->orderByDesc('distributed_at')
            ->orderByDesc('id')
            ->paginate($this->recordes);

        return view('livewire.manual-tracking.distribution.list-manual-distribution', [
            'distributions' => $distributions,
            'canCreate' => Gate::allows('create', ManualDistribution::class),
            'locationPicker' => $this->location()->picker($this->area),
        ])->extends('main.manual-tracking.index');
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
