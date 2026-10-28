<?php

namespace App\Http\Livewire\ManualTracking\Scan;

use App\Http\Livewire\Concerns\AuthorizesPermissions;
use App\Models\ManualTracking\ManualBook;
use App\Models\ManualTracking\ManualScan;
use App\Models\Organization;
use App\Services\ManualTracking\ManualScanService;
use App\Services\ManualTracking\ManualTrackingException;
use App\Support\ScopedLocation;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * Sorting bay at a warehouse or hub: scan each package, see which zone it goes to, and
 * get the count per zone and title to build the outgoing shipments from.
 */
class SortPackages extends Component
{
    use AuthorizesPermissions;

    public $area = ['region_id' => '', 'zone_id' => '', 'woreda_id' => ''];
    public $organizationId = '';
    public $titleId = '';
    public $since;
    public $lastScanId;

    public function mount()
    {
        // Sorting prepares outgoing shipments, which schools do not send
        abort_if(Auth::user()->isSchoolUser(), 403);

        $this->area = $this->location()->apply([]);
        $this->organizationId = $this->location()->organization($this->area, Auth::user()->organization_id);
        $this->since = now()->toDateString();
    }

    public function updatedArea($value, $key)
    {
        $this->area = $this->location()->apply($this->area, $key);
        $this->updatedOrganizationId();
    }

    public function updatedOrganizationId()
    {
        $this->organizationId = $this->location()->organization($this->area, $this->organizationId);
        $this->reset('titleId', 'lastScanId');
    }

    public function render()
    {
        $actor = Auth::user();
        $place = $this->place();
        $scans = ManualScan::query()->live()
            ->where('context', ManualScan::CONTEXT_SORT)
            ->where('organization_id', $place?->id ?? 0)
            ->where('scanned_at', '>=', $this->sinceDate());

        return view('livewire.manual-tracking.scan.sort-packages', [
            'locationPicker' => $this->location()->picker($this->area),
            'place' => $place,
            'titles' => ManualBook::query()
                ->when($place?->region_id, fn ($query) => $query->where(fn ($match) => $match->whereNull('region_id')->orWhere('region_id', $place->region_id)))
                ->orderBy('title')
                ->get(['id', 'title']),
            'tally' => (clone $scans)
                ->where('kind', ManualScan::KIND_PACKAGE)
                ->with(['zone:id,name', 'book:id,title'])
                ->selectRaw('zone_id, manual_book_id, COUNT(*) as packages, SUM(alert_status IS NOT NULL) as alerts')
                ->groupBy('zone_id', 'manual_book_id')
                ->get()
                ->sortBy(fn ($row) => ($row->zone->name ?? '~') . ($row->book->title ?? '~'))
                ->values(),
            'recent' => (clone $scans)->with(['zone:id,name', 'book:id,title'])->latest('id')->limit(15)->get(),
            'last' => $this->lastScanId ? ManualScan::query()->with(['zone:id,name', 'book:id,title'])->find($this->lastScanId) : null,
        ])->extends('main.manual-tracking.index');
    }

    public function scan($payload, $clientUuid = null)
    {
        $scanner = app(ManualScanService::class);
        $place = $this->place();

        if (! $place) {
            return $this->feedback(false, 'Choose where you are sorting first.');
        }

        try {
            $scan = $scanner->record(Auth::user(), $place, (string) $payload, ManualScan::CONTEXT_SORT, [
                'title' => $this->titleId ? ManualBook::query()->find($this->titleId) : null,
                'client_uuid' => $clientUuid,
            ]);
        } catch (ManualTrackingException $exception) {
            return $this->feedback(false, $exception->getMessage());
        }

        // Scanning a loose copy or the label's book code switches the title being counted
        if ($scan->kind === ManualScan::KIND_BOOK && $scan->manual_book_id) {
            $this->titleId = (string) $scan->manual_book_id;
        }

        $this->lastScanId = $scan->id;
        $this->feedback(! $scan->flag);
    }

    public function void($id, ManualScanService $scanner)
    {
        $scan = ManualScan::query()->where('organization_id', $this->place()?->id ?? 0)->findOrFail($id);

        try {
            $scanner->void(Auth::user(), $scan);
        } catch (ManualTrackingException $exception) {
            return $this->feedback(false, $exception->getMessage());
        }

        if ((int) $this->lastScanId === (int) $id) {
            $this->lastScanId = null;
        }
    }

    private function location(): ScopedLocation
    {
        return ScopedLocation::for(Auth::user());
    }

    private function place(): ?Organization
    {
        return $this->organizationId
            ? Organization::query()->accessibleBy(Auth::user())->find($this->organizationId)
            : null;
    }

    private function sinceDate()
    {
        try {
            return Carbon::parse($this->since ?: 'today')->startOfDay();
        } catch (\Throwable) {
            return now()->startOfDay();
        }
    }

    private function feedback(bool $ok, ?string $error = null): void
    {
        $this->dispatchBrowserEvent('scan-feedback', ['ok' => $ok]);

        if ($error) {
            $this->dispatchBrowserEvent('alert', ['type' => 'error', 'message' => $error]);
        }
    }

    protected function permissionMap(): array
    {
        return ['view' => 'manual-tracking.scan.sort'];
    }
}
