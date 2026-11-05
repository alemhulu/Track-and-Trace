<?php

namespace App\Http\Livewire\ManualTracking\Scan;

use App\Http\Livewire\Concerns\AuthorizesPermissions;
use App\Models\ManualTracking\ManualScan;
use App\Services\ManualTracking\ManualScanService;
use App\Services\ManualTracking\ManualTrackingException;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
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
    use AuthorizesPermissions;

    public $statusFilter = ManualScan::ALERT_OPEN;
    public $flagFilter = '';
    public $contextFilter = '';
    public $search = '';
    public $dateFrom = '';
    public $dateTo = '';

    protected $queryString = ['statusFilter' => ['except' => ManualScan::ALERT_OPEN], 'flagFilter' => ['except' => ''], 'search' => ['except' => '']];

    // Resolve notes, keyed by scan id
    public $notes = [];

    public function updating($property)
    {
        if (in_array($property, ['statusFilter', 'flagFilter', 'contextFilter', 'search', 'dateFrom', 'dateTo'], true)) {
            $this->resetPage();
        }
    }

    // Tiles toggle their filter: a second click shows everything again
    public function setFilter(string $filter, string $value): void
    {
        if (in_array($filter, ['statusFilter', 'flagFilter'], true)) {
            $this->{$filter} = $this->{$filter} === $value ? '' : $value;
            $this->resetPage();
        }
    }

    public function clearFilters(): void
    {
        $this->reset('flagFilter', 'contextFilter', 'search', 'dateFrom', 'dateTo');
        $this->statusFilter = '';
        $this->resetPage();
    }

    public function render()
    {
        $alerts = $this->filtered()
            ->when($this->statusFilter, fn ($query) => $query->where('alert_status', $this->statusFilter))
            ->when($this->flagFilter, fn ($query) => $query->where('flag', $this->flagFilter))
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
            'stats' => $this->stats(),
            'filtersActive' => $this->statusFilter !== ManualScan::ALERT_OPEN || $this->flagFilter || $this->contextFilter
                || trim($this->search) !== '' || $this->dateFrom || $this->dateTo,
        ])->extends('main.manual-tracking.index');
    }

    /**
     * Live alerts the user can see, narrowed by everything except status and problem, whose
     * counts the tiles show.
     */
    private function filtered(): Builder
    {
        $search = trim($this->search);
        $date = fn ($value) => is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1;

        return ManualScan::query()
            ->accessibleBy(Auth::user())
            ->live()
            ->whereNotNull('alert_status')
            ->when($this->contextFilter, fn ($query) => $query->where('context', $this->contextFilter))
            ->when($search !== '', fn ($query) => $query->where(fn ($match) => $match
                ->where('payload', 'like', '%' . $search . '%')
                ->orWhere('flag_message', 'like', '%' . $search . '%')
                ->orWhereHas('organization', fn ($place) => $place->where('name', 'like', '%' . $search . '%'))
                ->orWhereHas('book', fn ($book) => $book->where('title', 'like', '%' . $search . '%'))
                ->orWhereHas('distribution', fn ($shipment) => $shipment->where('reference', 'like', '%' . $search . '%'))))
            ->when($date($this->dateFrom), fn ($query) => $query->where('scanned_at', '>=', Carbon::parse($this->dateFrom)->startOfDay()))
            ->when($date($this->dateTo), fn ($query) => $query->where('scanned_at', '<=', Carbon::parse($this->dateTo)->endOfDay()));
    }

    private function stats(): array
    {
        $byStatus = $this->filtered()->when($this->flagFilter, fn ($query) => $query->where('flag', $this->flagFilter))
            ->toBase()->groupBy('alert_status')->selectRaw('alert_status, COUNT(*) as total')->pluck('total', 'alert_status');
        $byFlag = $this->filtered()->when($this->statusFilter, fn ($query) => $query->where('alert_status', $this->statusFilter))
            ->toBase()->groupBy('flag')->selectRaw('flag, COUNT(*) as total')->pluck('total', 'flag');
        $open = fn () => $this->filtered()->when($this->flagFilter, fn ($query) => $query->where('flag', $this->flagFilter))
            ->where('alert_status', ManualScan::ALERT_OPEN);
        $hours = $this->filtered()->when($this->flagFilter, fn ($query) => $query->where('flag', $this->flagFilter))
            ->where('alert_status', ManualScan::ALERT_RESOLVED)->whereNotNull('resolved_at')
            ->toBase()->selectRaw('AVG(TIMESTAMPDIFF(HOUR, scanned_at, resolved_at)) as hours')->value('hours');

        return [
            'open' => (int) ($byStatus[ManualScan::ALERT_OPEN] ?? 0),
            'resolved' => (int) ($byStatus[ManualScan::ALERT_RESOLVED] ?? 0),
            'flags' => collect(ManualScan::ALERT_FLAGS)->mapWithKeys(fn ($flag) => [$flag => (int) ($byFlag[$flag] ?? 0)])->all(),
            'open_places' => (int) $open()->distinct()->count('organization_id'),
            'open_shipments' => (int) $open()->whereNotNull('manual_distribution_id')->distinct()->count('manual_distribution_id'),
            'close_days' => $hours !== null ? round($hours / 24, 1) : null,
        ];
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

    protected function permissionMap(): array
    {
        return [
            'view' => 'manual-tracking.view',
            'actions' => [
                'resolve' => 'manual-tracking.alerts.resolve',
            ],
        ];
    }
}
