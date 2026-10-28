<?php

namespace App\Http\Livewire\ManualTracking\Scan;

use App\Models\ManualTracking\ManualScan;
use App\Services\ManualTracking\ManualScanService;
use App\Services\ManualTracking\ManualTrackingException;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Packages scanned somewhere they should not be. Closed by someone other than the scanner
 * once the package has been sent on or the allocation corrected.
 */
class ScanAlertIndex extends Component
{
    use WithPagination;

    public $statusFilter = ManualScan::ALERT_OPEN;

    // Resolve notes, keyed by scan id
    public $notes = [];

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function render()
    {
        $alerts = ManualScan::query()
            ->accessibleBy(Auth::user())
            ->live()
            ->whereNotNull('alert_status')
            ->when($this->statusFilter, fn ($query) => $query->where('alert_status', $this->statusFilter))
            ->with([
                'organization:id,name',
                'zone:id,name',
                'book:id,title',
                'distribution:id,reference,organization_id',
                'distribution.organization:id,name',
                'scanner:id,name',
                'resolver:id,name',
            ])
            ->orderByDesc('id')
            ->paginate(15);

        return view('livewire.manual-tracking.scan.scan-alert-index', [
            'alerts' => $alerts,
        ])->extends('main.manual-tracking.index');
    }

    public function resolve($id, ManualScanService $scanner)
    {
        $scan = ManualScan::query()->accessibleBy(Auth::user())->findOrFail($id);

        try {
            $scanner->resolve(Auth::user(), $scan, (string) ($this->notes[$id] ?? ''));
        } catch (ManualTrackingException $exception) {
            return $this->dispatchBrowserEvent('alert', ['type' => 'error', 'message' => $exception->getMessage()]);
        }

        unset($this->notes[$id]);
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Alert closed.']);
    }
}
