<?php

namespace App\Http\Livewire\ManualTracking\Discrepancy;

use App\Models\ManualTracking\ManualDiscrepancy;
use App\Services\ManualTracking\ManualShipmentService;
use App\Services\ManualTracking\ManualTrackingException;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class ManualDiscrepancyIndex extends Component
{
    use WithPagination;

    public $statusFilter = ManualDiscrepancy::STATUS_OPEN;

    // Resolve form state, keyed by discrepancy id
    public $resolutions = [];
    public $notes = [];

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function render()
    {
        $discrepancies = ManualDiscrepancy::query()
            ->accessibleBy(Auth::user())
            ->with([
                'line.book:id,title',
                'line.distribution:id,reference,organization_id,destination_organization_id,distributed_by,received_by',
                'line.distribution.organization:id,name',
                'line.distribution.destinationOrganization:id,name',
                'raisedBy:id,name',
                'resolvedBy:id,name',
            ])
            ->when($this->statusFilter, fn ($query) => $query->where('status', $this->statusFilter))
            ->orderByDesc('id')
            ->paginate(15);

        return view('livewire.manual-tracking.discrepancy.manual-discrepancy-index', [
            'discrepancies' => $discrepancies,
        ])->extends('main.manual-tracking.index');
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
}
