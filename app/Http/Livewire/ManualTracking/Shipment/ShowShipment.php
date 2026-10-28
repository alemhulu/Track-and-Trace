<?php

namespace App\Http\Livewire\ManualTracking\Shipment;

use App\Models\ManualTracking\ManualDistribution;
use App\Models\ManualTracking\ManualScan;
use App\Services\ManualTracking\ManualScanService;
use App\Services\ManualTracking\ManualShipmentService;
use App\Services\ManualTracking\ManualStockService;
use App\Services\ManualTracking\ManualTrackingException;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ShowShipment extends Component
{
    public $shipmentId;

    // Receive form, keyed by line id
    public $counts = [];
    public $receiptNotes;

    // 'packages' or 'totals', as on the shipment form
    public $receiveMode = 'packages';

    // Title that package scans are counted against while receiving
    public $scanTitleId = '';
    public $lastScanId;

    public function mount($shipment)
    {
        $model = ManualDistribution::query()->accessibleBy(Auth::user())->findOrFail($shipment);
        $this->shipmentId = $model->id;

        if ($model->lines->count() === 1) {
            $this->scanTitleId = (string) $model->lines->first()->manual_book_id;
        }

        foreach ($model->lines as $line) {
            $this->counts[$line->id] = [
                'full_packages' => $line->full_packages,
                'partial_package_books' => $line->partial_package_books,
                'partial_teacher_guides' => $line->partial_teacher_guides,
                'student_books' => $line->quantity,
                'teacher_guides' => $line->teacher_guides,
                'damaged_quantity' => 0,
                'damaged_teacher_guides' => 0,
            ];
        }
    }

    public function render(ManualStockService $stock)
    {
        $shipment = $this->shipment();
        $actor = Auth::user();

        $canReceive = $shipment->isInTransit()
            && $shipment->destinationOrganization
            && (int) $shipment->distributed_by !== (int) $actor->id
            && $stock->canActFor($actor, $shipment->destinationOrganization);

        $scans = ManualScan::query()->live()->where('manual_distribution_id', $shipment->id);

        return view('livewire.manual-tracking.shipment.show-shipment', [
            'shipment' => $shipment,
            'canReceive' => $canReceive,
            'scanCounts' => app(ManualScanService::class)->packageCounts($shipment),
            'recentScans' => (clone $scans)->with(['zone:id,name', 'book:id,title'])->latest('id')->limit(10)->get(),
            'scanAlerts' => (clone $scans)->whereNotNull('alert_status')->count(),
            'lastScan' => $this->lastScanId ? ManualScan::query()->with(['zone:id,name', 'book:id,title'])->find($this->lastScanId) : null,
            'receivedPreview' => $shipment->lines->mapWithKeys(fn ($line) => [$line->id => $this->countedCopies($line)]),
        ])->extends('main.manual-tracking.index');
    }

    public function receive(ManualShipmentService $shipments)
    {
        $this->validate([
            'counts.*.full_packages' => 'required|integer|min:0',
            'counts.*.partial_package_books' => 'required|integer|min:0',
            'counts.*.partial_teacher_guides' => 'required|integer|min:0',
            'counts.*.student_books' => 'required|integer|min:0',
            'counts.*.teacher_guides' => 'required|integer|min:0',
            'counts.*.damaged_quantity' => 'required|integer|min:0',
            'counts.*.damaged_teacher_guides' => 'required|integer|min:0',
            'receiptNotes' => 'nullable|string|max:1000',
        ]);

        $fields = $this->receiveMode === 'totals'
            ? ['student_books', 'teacher_guides', 'damaged_quantity', 'damaged_teacher_guides']
            : ['full_packages', 'partial_package_books', 'partial_teacher_guides', 'damaged_quantity', 'damaged_teacher_guides'];
        $counts = collect($this->counts)->map(fn ($count) => collect($count)->only($fields)->all())->all();

        try {
            $received = $shipments->receive(Auth::user(), $this->shipment(), $counts, $this->receiptNotes ?: null);
        } catch (ManualTrackingException $exception) {
            return $this->dispatchBrowserEvent('alert', ['type' => 'error', 'message' => $exception->getMessage()]);
        }

        $message = $received->status === ManualDistribution::STATUS_RECEIVED
            ? 'Receipt confirmed. Stock added.'
            : 'Receipt confirmed with discrepancies. They are listed below for resolution.';

        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => $message]);
    }

    public function scan($payload, $clientUuid = null)
    {
        $shipment = $this->shipment();

        if (! $shipment->destinationOrganization) {
            return $this->dispatchBrowserEvent('alert', ['type' => 'error', 'message' => 'This shipment has no destination to receive at.']);
        }

        try {
            $scan = app(ManualScanService::class)->record(Auth::user(), $shipment->destinationOrganization, (string) $payload, ManualScan::CONTEXT_RECEIVE, [
                'shipment' => $shipment,
                'title' => $shipment->lines->firstWhere('manual_book_id', (int) $this->scanTitleId)?->book,
                'client_uuid' => $clientUuid,
            ]);
        } catch (ManualTrackingException $exception) {
            $this->dispatchBrowserEvent('scan-feedback', ['ok' => false]);

            return $this->dispatchBrowserEvent('alert', ['type' => 'error', 'message' => $exception->getMessage()]);
        }

        if ($scan->kind === ManualScan::KIND_BOOK && $shipment->lines->contains('manual_book_id', $scan->manual_book_id)) {
            $this->scanTitleId = (string) $scan->manual_book_id;
        }

        $this->lastScanId = $scan->id;
        $this->dispatchBrowserEvent('scan-feedback', ['ok' => ! $scan->flag]);
    }

    public function voidScan($id)
    {
        $scan = ManualScan::query()->where('manual_distribution_id', $this->shipmentId)->findOrFail($id);

        try {
            app(ManualScanService::class)->void(Auth::user(), $scan);
        } catch (ManualTrackingException $exception) {
            return $this->dispatchBrowserEvent('alert', ['type' => 'error', 'message' => $exception->getMessage()]);
        }
    }

    /**
     * Fill the receipt counts from the packages scanned. A package QR cannot tell a full
     * package from the partial one, so a title is taken as complete when every package was
     * scanned; otherwise the scans are counted as full packages for the receiver to adjust.
     */
    public function applyScans()
    {
        $shipment = $this->shipment();
        $scanned = app(ManualScanService::class)->packageCounts($shipment);
        $short = [];

        foreach ($shipment->lines as $line) {
            $count = $scanned[$line->manual_book_id] ?? 0;
            if (! $count) {
                continue;
            }

            $sentPackages = $line->full_packages + ($line->partial_package_books || $line->partial_teacher_guides ? 1 : 0);

            if ($count >= $sentPackages) {
                $this->counts[$line->id]['full_packages'] = $line->full_packages + ($count - $sentPackages);
                $this->counts[$line->id]['partial_package_books'] = $line->partial_package_books;
                $this->counts[$line->id]['partial_teacher_guides'] = $line->partial_teacher_guides;
            } else {
                $this->counts[$line->id]['full_packages'] = $count;
                $this->counts[$line->id]['partial_package_books'] = 0;
                $this->counts[$line->id]['partial_teacher_guides'] = 0;
                $short[] = $line->book->title;
            }
        }

        $this->syncTotals($shipment);

        $message = $short
            ? 'Counts filled from scans. Fewer packages than sent for: ' . implode(', ', $short) . '. Check whether the partial package arrived.'
            : 'Counts filled from scans. Titles with no scans were left as they were.';

        $this->dispatchBrowserEvent('alert', ['type' => $short ? 'warning' : 'success', 'message' => $message]);
    }

    // Carry the numbers over when switching, so nothing typed is lost
    public function updatedReceiveMode()
    {
        $this->receiveMode = $this->receiveMode === 'totals' ? 'totals' : 'packages';
        $shipment = $this->shipment();

        if ($this->receiveMode === 'totals') {
            return $this->syncTotals($shipment);
        }

        foreach ($shipment->lines as $line) {
            $this->counts[$line->id] = array_merge($this->counts[$line->id], $line->book->packagesFor(
                (int) $this->counts[$line->id]['student_books'],
                (int) $this->counts[$line->id]['teacher_guides']
            ));
        }
    }

    private function syncTotals(ManualDistribution $shipment): void
    {
        foreach ($shipment->lines as $line) {
            $copies = $line->book->copiesFor(
                (int) $this->counts[$line->id]['full_packages'],
                (int) $this->counts[$line->id]['partial_package_books'],
                (int) $this->counts[$line->id]['partial_teacher_guides']
            );
            $this->counts[$line->id]['student_books'] = $copies['student'];
            $this->counts[$line->id]['teacher_guides'] = $copies['teacher'];
        }
    }

    private function countedCopies($line): array
    {
        $count = $this->counts[$line->id] ?? [];

        return $this->receiveMode === 'totals'
            ? ['student' => max(0, (int) ($count['student_books'] ?? 0)), 'teacher' => max(0, (int) ($count['teacher_guides'] ?? 0))]
            : $line->book->copiesFor((int) ($count['full_packages'] ?? 0), (int) ($count['partial_package_books'] ?? 0), (int) ($count['partial_teacher_guides'] ?? 0));
    }

    private function shipment(): ManualDistribution
    {
        return ManualDistribution::query()
            ->accessibleBy(Auth::user())
            ->with([
                'lines.book',
                'lines.discrepancies',
                'organization',
                'destinationOrganization',
                'dispatcher:id,name',
                'receiver:id,name',
            ])
            ->findOrFail($this->shipmentId);
    }
}
