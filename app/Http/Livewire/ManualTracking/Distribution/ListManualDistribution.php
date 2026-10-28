<?php

namespace App\Http\Livewire\ManualTracking\Distribution;

use App\Models\ManualTracking\ManualDistribution;
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

    public $recordes = 10;
    public $statusFilter = '';
    public $search = '';

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
        ])->extends('main.manual-tracking.index');
    }
}
