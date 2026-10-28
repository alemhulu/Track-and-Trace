<?php

namespace App\Http\Livewire\ManualTracking\Shipment;

use App\Http\Livewire\Concerns\AuthorizesPermissions;
use App\Models\ManualTracking\ManualBook;
use App\Models\ManualTracking\ManualDistribution;
use App\Models\ManualTracking\ManualScan;
use App\Models\ManualTracking\ManualStockBalance;
use App\Models\Organization;
use App\Models\Zone;
use App\Services\ManualTracking\ManualScanService;
use App\Services\ManualTracking\ManualShipmentService;
use App\Services\ManualTracking\ManualTrackingException;
use App\Support\ScopedLocation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Livewire\Component;

class CreateShipment extends Component
{
    use AuthorizesPermissions;

    // Where the books leave from and go to; levels fixed by the user's access level stay locked
    public $source = ['region_id' => '', 'zone_id' => '', 'woreda_id' => ''];
    public $from_organization_id = '';
    public $destination = ['region_id' => '', 'zone_id' => '', 'woreda_id' => ''];
    public $to_organization_id = '';
    public $carrier;
    public $vehicle_no;
    public $driver_name;
    public $driver_phone;
    public $waybill_no;
    public $expected_at;
    public $remarks;
    public $lines = [];

    // 'packages' counts full and partial packages; 'totals' takes the total copies of each edition
    public $entryMode = 'packages';

    // Line that package scans are added to, and warnings from scans that do not fit the destination
    public $activeLine = 0;
    public $scanWarnings = [];

    // One id per form, so a double-submit or retry cannot create two shipments
    public $clientUuid;

    public function mount()
    {
        if (Gate::denies('create', ManualDistribution::class)) {
            abort(403);
        }

        $this->clientUuid = (string) Str::uuid();
        $this->addLine();

        $actor = Auth::user();
        $this->source = $this->sourceScope()->apply([]);
        $this->from_organization_id = $this->validSource($actor->organization_id);

        $this->prefillFromSorting($actor);
        $this->destination = $this->destinationScope()->apply($this->destination);
    }

    // Carry the numbers over when switching, so nothing typed is lost
    public function updatedEntryMode()
    {
        $this->entryMode = $this->entryMode === 'totals' ? 'totals' : 'packages';
        $books = ManualBook::query()->whereKey(collect($this->lines)->pluck('manual_book_id')->filter())->get()->keyBy('id');

        foreach ($this->lines as $index => $line) {
            $book = $books[$line['manual_book_id']] ?? null;
            if (! $book) {
                continue;
            }

            if ($this->entryMode === 'totals') {
                $copies = $book->copiesFor((int) $line['full_packages'], (int) $line['partial_package_books'], (int) $line['partial_teacher_guides']);
                $this->lines[$index]['student_books'] = $copies[ManualBook::EDITION_STUDENT];
                $this->lines[$index]['teacher_guides'] = $copies[ManualBook::EDITION_TEACHER];
            } else {
                $this->lines[$index] = array_merge($line, $book->packagesFor((int) $line['student_books'], (int) $line['teacher_guides']));
            }
        }
    }

    public function updatedSource($value, $key)
    {
        $this->source = $this->sourceScope()->apply($this->source, $key);
        $this->updatedFromOrganizationId();
    }

    // A new source can change where it may send to (printers and hubs send anywhere)
    public function updatedFromOrganizationId()
    {
        $this->from_organization_id = $this->validSource($this->from_organization_id);
        $this->destination = $this->destinationScope()->apply($this->destination);
        $this->updatedToOrganizationId();
    }

    public function updatedDestination($value, $key)
    {
        $this->destination = $this->destinationScope()->apply($this->destination, $key);
        $this->updatedToOrganizationId();
    }

    public function updatedToOrganizationId()
    {
        $to = $this->destinationScope()->organization($this->destination, $this->to_organization_id);
        $this->to_organization_id = (string) $to === (string) $this->from_organization_id ? '' : $to;
    }

    /**
     * A book QR picks the title line; a package QR adds a full package to that line and is
     * checked against the destination before anything leaves.
     */
    public function scan($payload, $clientUuid = null)
    {
        $destination = $this->to_organization_id ? Organization::query()->find($this->to_organization_id) : null;
        $result = app(ManualScanService::class)->identify((string) $payload, $destination);

        if ($result['kind'] === ManualScan::KIND_BOOK) {
            $this->activeLine = $this->lineFor($result['book']->id);
            $this->scanFeedback(! $result['flag'], $result['flag'] ? $result['message'] : null);

            return;
        }

        if ($result['kind'] !== ManualScan::KIND_PACKAGE) {
            return $this->scanFeedback(false, $result['message']);
        }

        if (empty($this->lines[$this->activeLine]['manual_book_id'])) {
            return $this->scanFeedback(false, 'Choose the title or scan one of its books before scanning packages.');
        }

        if ($this->entryMode === 'totals') {
            $book = ManualBook::query()->find($this->lines[$this->activeLine]['manual_book_id']);
            $this->lines[$this->activeLine]['student_books'] = (int) $this->lines[$this->activeLine]['student_books'] + $book->students_per_package;
            $this->lines[$this->activeLine]['teacher_guides'] = (int) $this->lines[$this->activeLine]['teacher_guides'] + ($book->has_teacher_guide ? 1 : 0);
        } else {
            $this->lines[$this->activeLine]['full_packages'] = (int) $this->lines[$this->activeLine]['full_packages'] + 1;
        }
        $this->scanFeedback(! $result['flag'], $result['flag'] ? $result['message'] : null);
    }

    public function setActiveLine($index)
    {
        $this->activeLine = isset($this->lines[$index]) ? (int) $index : 0;
    }

    public function addLine()
    {
        $this->lines[] = ['manual_book_id' => '', 'full_packages' => 0, 'partial_package_books' => 0, 'partial_teacher_guides' => 0, 'student_books' => 0, 'teacher_guides' => 0];
    }

    public function removeLine($index)
    {
        unset($this->lines[$index]);
        $this->lines = array_values($this->lines);
        $this->activeLine = min($this->activeLine, max(count($this->lines) - 1, 0));
    }

    public function render()
    {
        $actor = Auth::user();
        $openDestinations = $this->sendsAnywhere();
        $books = ManualBook::query()->when(! $openDestinations, fn ($query) => $query->accessibleBy($actor))->orderBy('title')->get(['id', 'title', 'has_teacher_guide', 'students_per_package'])->keyBy('id');
        $destinations = $this->destinationScope()->organizations($this->destination)
            ->when($this->from_organization_id, fn ($query) => $query->whereKeyNot($this->from_organization_id));

        // Shipments stay inside the user's own area, except from printers and hubs, which deliver anywhere
        return view('livewire.manual-tracking.shipment.create-shipment', [
            'sourcePicker' => $this->sourceScope()->picker($this->source, $this->sources()),
            'destinationPicker' => $this->destinationScope()->picker($this->destination, $destinations),
            'openDestinations' => $openDestinations,
            'books' => $books,
            'available' => $this->availableAtSource(),
            'lineCopies' => collect($this->lines)->map(function ($line) use ($books) {
                $book = $books[$line['manual_book_id']] ?? null;

                if (! $book) {
                    return ['student' => 0, 'teacher' => 0];
                }

                return $this->entryMode === 'totals'
                    ? ['student' => max(0, (int) $line['student_books']), 'teacher' => max(0, (int) $line['teacher_guides'])]
                    : $book->copiesFor((int) $line['full_packages'], (int) $line['partial_package_books'], (int) $line['partial_teacher_guides']);
            })->all(),
        ])->extends('main.manual-tracking.index');
    }

    public function dispatchShipment(ManualShipmentService $shipments)
    {
        $this->validate([
            'from_organization_id' => 'required|exists:organizations,id',
            'to_organization_id' => 'required|exists:organizations,id|different:from_organization_id',
            'carrier' => 'nullable|string|max:255',
            'vehicle_no' => 'nullable|string|max:50',
            'driver_name' => 'nullable|string|max:255',
            'driver_phone' => 'nullable|string|max:30',
            'waybill_no' => 'nullable|string|max:100',
            'expected_at' => 'nullable|date',
            'remarks' => 'nullable|string|max:1000',
            'lines' => 'required|array|min:1',
            'lines.*.manual_book_id' => 'required|exists:manual_books,id',
        ] + ($this->entryMode === 'totals' ? [
            'lines.*.student_books' => 'required|integer|min:0',
            'lines.*.teacher_guides' => 'required|integer|min:0',
        ] : [
            'lines.*.full_packages' => 'required|integer|min:0',
            'lines.*.partial_package_books' => 'required|integer|min:0',
            'lines.*.partial_teacher_guides' => 'required|integer|min:0',
        ]), [], [
            'from_organization_id' => 'source',
            'to_organization_id' => 'destination',
            'lines.*.manual_book_id' => 'title',
            'lines.*.student_books' => 'student books',
            'lines.*.teacher_guides' => 'teacher guides',
        ]);

        try {
            $shipment = $shipments->dispatch(Auth::user(), [
                'from_organization_id' => (int) $this->from_organization_id,
                'to_organization_id' => (int) $this->to_organization_id,
                'lines' => collect($this->lines)->map(fn ($line) => $this->entryMode === 'totals'
                    ? collect($line)->only(['manual_book_id', 'student_books', 'teacher_guides'])->all()
                    : collect($line)->only(['manual_book_id', 'full_packages', 'partial_package_books', 'partial_teacher_guides'])->all()
                )->all(),
                'carrier' => $this->carrier,
                'vehicle_no' => $this->vehicle_no,
                'driver_name' => $this->driver_name,
                'driver_phone' => $this->driver_phone,
                'waybill_no' => $this->waybill_no,
                'expected_at' => $this->expected_at ?: null,
                'remarks' => $this->remarks,
                'client_uuid' => $this->clientUuid,
            ]);
        } catch (ManualTrackingException $exception) {
            return $this->dispatchBrowserEvent('alert', ['type' => 'error', 'message' => $exception->getMessage()]);
        }

        session()->flash('success', "Shipment {$shipment->reference} dispatched.");

        return redirect()->route('manual-tracking.shipments.show', $shipment->id);
    }

    // "Ship to zone" on the sorting screen opens this form with the sorted packages filled in
    private function prefillFromSorting($actor): void
    {
        $query = request()->query();

        if (! empty($query['from']) && ($from = $this->validSource($query['from']))) {
            $this->from_organization_id = $from;
        }

        if (! empty($query['zone']) && ($zone = Zone::query()->find($query['zone']))) {
            $this->destination = ['region_id' => (string) $zone->region_id, 'zone_id' => (string) $zone->id, 'woreda_id' => ''];
        }

        if (! empty($query['book']) && ManualBook::query()->accessibleBy($actor)->whereKey($query['book'])->exists()) {
            $this->lines[0]['manual_book_id'] = (int) $query['book'];
            $this->lines[0]['full_packages'] = max(0, (int) ($query['packages'] ?? 0));
        }
    }

    private function lineFor(int $bookId): int
    {
        foreach ($this->lines as $index => $line) {
            if ((int) $line['manual_book_id'] === $bookId) {
                return $index;
            }
        }

        foreach ($this->lines as $index => $line) {
            if (! $line['manual_book_id']) {
                $this->lines[$index]['manual_book_id'] = $bookId;

                return $index;
            }
        }

        $this->addLine();
        $index = array_key_last($this->lines);
        $this->lines[$index]['manual_book_id'] = $bookId;

        return $index;
    }

    private function scanFeedback(bool $ok, ?string $warning = null): void
    {
        $this->dispatchBrowserEvent('scan-feedback', ['ok' => $ok]);

        if ($warning) {
            array_unshift($this->scanWarnings, $warning);
            $this->scanWarnings = array_slice(array_unique($this->scanWarnings), 0, 5);
            $this->dispatchBrowserEvent('alert', ['type' => 'warning', 'message' => $warning]);
        }
    }

    /**
     * @return array<int, array{student: int, teacher: int}> keyed by title id
     */
    private function availableAtSource(): array
    {
        if (! $this->from_organization_id) {
            return [];
        }

        return ManualStockBalance::query()
            ->where('organization_id', $this->from_organization_id)
            ->get(['manual_book_id', 'edition', 'on_hand'])
            ->groupBy('manual_book_id')
            ->map(fn ($rows) => [
                'student' => (int) $rows->firstWhere('edition', ManualBook::EDITION_STUDENT)?->on_hand,
                'teacher' => (int) $rows->firstWhere('edition', ManualBook::EDITION_TEACHER)?->on_hand,
            ])
            ->all();
    }

    private function sourceScope(): ScopedLocation
    {
        return ScopedLocation::for(Auth::user());
    }

    // Schools only receive, so they are never offered as a source
    private function sources()
    {
        return $this->sourceScope()->organizations($this->source)
            ->whereDoesntHave('organizationType', fn ($type) => $type->where('name', 'School'));
    }

    private function validSource($organizationId): string
    {
        $id = $this->sourceScope()->organization($this->source, $organizationId);

        return $id !== '' && $this->sources()->whereKey($id)->exists() ? $id : '';
    }

    private function destinationScope(): ScopedLocation
    {
        return $this->sendsAnywhere() ? ScopedLocation::open() : ScopedLocation::destinationFor(Auth::user());
    }

    private function sendsAnywhere(): bool
    {
        $source = $this->from_organization_id ? Organization::query()->with('organizationType')->find($this->from_organization_id) : null;

        return app(ManualShipmentService::class)->isSupplySource($source);
    }

    protected function permissionMap(): array
    {
        return ['view' => 'manual-tracking.shipments.dispatch'];
    }
}
