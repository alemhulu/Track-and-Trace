<?php

namespace App\Http\Livewire\ManualTracking\Shipment;

use App\Http\Livewire\Concerns\AuthorizesPermissions;
use App\Models\ManualTracking\ManualAllocation;
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

        // A woreda or zone office usually has a single place it can send from: choose it for them
        if ($this->from_organization_id === '' && ($only = $this->sources()->limit(2)->pluck('id'))->count() === 1) {
            $this->from_organization_id = (string) $only->first();
        }

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
                $copies = $book->copiesFor((int) $line['full_packages'], (int) $line['partial_package_books'], (int) $line['partial_teacher_guides'], (int) $line['extra_teacher_guides']);
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

        // Titles of another region cannot go to this destination: clear them from the lines
        $fits = $this->titles()->whereKey(collect($this->lines)->pluck('manual_book_id')->filter())->pluck('id')->map(fn ($id) => (int) $id);
        foreach ($this->lines as $index => $line) {
            if ($line['manual_book_id'] && ! $fits->contains((int) $line['manual_book_id'])) {
                $this->lines[$index]['manual_book_id'] = '';
            }
        }
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

    // Picking a title that is already on another line is flagged at once
    public function updatedLines($value, $key)
    {
        [$index, $field] = array_pad(explode('.', (string) $key, 2), 2, null);

        if ($field === 'manual_book_id' && $value && ($others = $this->duplicateLines()[(int) $index] ?? null)) {
            $title = ManualBook::query()->whereKey($value)->value('title');
            $this->dispatchBrowserEvent('alert', ['type' => 'warning', 'message' => sprintf(
                '%s is already on line %s. A title can be added only once: change the counts on that line instead.',
                $title, implode(', ', $others)
            )]);
        }
    }

    public function setActiveLine($index)
    {
        $this->activeLine = isset($this->lines[$index]) ? (int) $index : 0;
    }

    public function addLine()
    {
        $this->lines[] = ['manual_book_id' => '', 'full_packages' => 0, 'partial_package_books' => 0, 'partial_teacher_guides' => 0, 'extra_teacher_guides' => 0, 'student_books' => 0, 'teacher_guides' => 0];
    }

    public function removeLine($index)
    {
        unset($this->lines[$index]);
        $this->lines = array_values($this->lines);
        $this->activeLine = min($this->activeLine, max(count($this->lines) - 1, 0));
    }

    /**
     * Fill a line with what may still go to the destination: the allocation left there, no
     * more than the source holds, packed as full and partial packages plus extra teacher guides.
     */
    public function fillAllowed($index)
    {
        $line = $this->lines[$index] ?? null;
        $book = $line && $line['manual_book_id'] ? ManualBook::query()->find($line['manual_book_id']) : null;
        $room = $book ? $this->roomFor($book) : null;

        if (! $room) {
            return;
        }

        $this->lines[$index] = array_merge($line, $book->packagesFor($room['student'], $room['teacher']), [
            'student_books' => $room['student'],
            'teacher_guides' => $room['teacher'],
        ]);
        $this->activeLine = (int) $index;
    }

    public function render()
    {
        $actor = Auth::user();
        $openDestinations = $this->sendsAnywhere();
        $books = $this->titles()->orderBy('title')->get(['id', 'title', 'has_teacher_guide', 'students_per_package'])->keyBy('id');
        $destinations = $this->destinationScope()->organizations($this->destination)
            ->when($this->from_organization_id, fn ($query) => $query->whereKeyNot($this->from_organization_id));

        // Shipments stay inside the user's own area, except from printers and hubs, which deliver anywhere
        return view('livewire.manual-tracking.shipment.create-shipment', [
            'sourcePicker' => $this->sourceScope()->picker($this->source, $this->sources(), $this->from_organization_id),
            'destinationPicker' => $this->destinationScope()->picker($this->destination, $destinations, $this->to_organization_id),
            'openDestinations' => $openDestinations,
            'titleRegion' => ($titleRegion = $this->titleRegion())
                ? ['name' => \App\Models\Region::query()->whereKey($titleRegion['id'])->value('name'), 'side' => $titleRegion['side']]
                : null,
            'books' => $books,
            'duplicates' => $this->duplicateLines(),
            'available' => $this->availableAtSource(),
            'allocation' => $allocation = $this->allocationAtDestination(),
            'room' => collect($this->lines)->map(function ($line) use ($books, $allocation) {
                $book = $books[$line['manual_book_id']] ?? null;

                return $book && isset($allocation[$book->id]) ? $this->roomFor($book, $allocation) : null;
            })->all(),
            'lineCopies' => collect($this->lines)->map(function ($line) use ($books) {
                $book = $books[$line['manual_book_id']] ?? null;

                if (! $book) {
                    return ['student' => 0, 'teacher' => 0];
                }

                return $this->entryMode === 'totals'
                    ? ['student' => max(0, (int) $line['student_books']), 'teacher' => max(0, (int) $line['teacher_guides'])]
                    : $book->copiesFor((int) $line['full_packages'], (int) $line['partial_package_books'], (int) $line['partial_teacher_guides'], (int) ($line['extra_teacher_guides'] ?? 0));
            })->all(),
        ])->extends('main.manual-tracking.index');
    }

    public function dispatchShipment(ManualShipmentService $shipments)
    {
        if ($this->duplicateLines()) {
            return $this->dispatchBrowserEvent('alert', ['type' => 'error', 'message' => 'A title is on more than one line. Remove the repeated line or choose another title before dispatching.']);
        }

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
            'lines.*.extra_teacher_guides' => 'required|integer|min:0',
        ]), [], [
            'from_organization_id' => 'source',
            'to_organization_id' => 'destination',
            'lines.*.manual_book_id' => 'title',
            'lines.*.student_books' => 'student books',
            'lines.*.teacher_guides' => 'teacher guides',
            'lines.*.extra_teacher_guides' => 'extra teacher guides',
        ]);

        try {
            $shipment = $shipments->dispatch(Auth::user(), [
                'from_organization_id' => (int) $this->from_organization_id,
                'to_organization_id' => (int) $this->to_organization_id,
                'lines' => collect($this->lines)->map(fn ($line) => $this->entryMode === 'totals'
                    ? collect($line)->only(['manual_book_id', 'student_books', 'teacher_guides'])->all()
                    : collect($line)->only(['manual_book_id', 'full_packages', 'partial_package_books', 'partial_teacher_guides', 'extra_teacher_guides'])->all()
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

    /**
     * Lines whose title is also on another line, keyed by line index, with the other lines'
     * numbers as shown on screen (counting from 1).
     *
     * @return array<int, array<int, int>>
     */
    private function duplicateLines(): array
    {
        $byTitle = collect($this->lines)->filter(fn ($line) => filled($line['manual_book_id']))
            ->groupBy(fn ($line) => (string) $line['manual_book_id'], true)
            ->filter(fn ($lines) => $lines->count() > 1);
        $duplicates = [];

        foreach ($byTitle as $lines) {
            foreach ($lines->keys() as $index) {
                $duplicates[$index] = $lines->keys()->reject(fn ($other) => $other === $index)->map(fn ($other) => $other + 1)->values()->all();
            }
        }

        return $duplicates;
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
     * Copies of a title that may still be sent: the destination's allocation less what has been
     * sent in, and no more than the source holds. Null when the destination has no allocation.
     *
     * @return array{student: int, teacher: int}|null
     */
    private function roomFor(ManualBook $book, ?array $allocation = null): ?array
    {
        $target = ($allocation ?? $this->allocationAtDestination())[$book->id] ?? null;
        if (! $target) {
            return null;
        }

        $stock = $this->from_organization_id ? ($this->availableAtSource()[$book->id] ?? ['student' => 0, 'teacher' => 0]) : null;
        $left = [
            'student' => max(0, $target['student'] - $target['sent_student']),
            'teacher' => $book->has_teacher_guide ? max(0, $target['teacher'] - $target['sent_teacher']) : 0,
        ];

        return $stock === null ? $left : [
            'student' => min($left['student'], $stock['student']),
            'teacher' => min($left['teacher'], $stock['teacher']),
            'limited' => $stock['student'] < $left['student'] || $stock['teacher'] < $left['teacher'],
        ];
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

    /**
     * For shipments into a zone or region from outside it: each title's allocation there for
     * the latest year and how much has already been sent in, so over-sending shows before
     * dispatch. A zone's own share is used when the zone has one, otherwise the region's.
     *
     * @return array<int, array{level: string, student: int, teacher: int, sent_student: int, sent_teacher: int}>
     */
    private function allocationAtDestination(): array
    {
        $bookIds = collect($this->lines)->pluck('manual_book_id')->filter()->map(fn ($id) => (int) $id)->unique()->values()->all();
        $to = $this->to_organization_id ? Organization::query()->find($this->to_organization_id, ['id', 'region_id', 'zone_id']) : null;
        $from = $this->from_organization_id ? Organization::query()->find($this->from_organization_id, ['id', 'region_id', 'zone_id']) : null;
        $regionId = (int) ($to?->region_id ?: $this->destination['region_id']);
        $zoneId = (int) ($to?->zone_id ?: $this->destination['zone_id']);

        if (! $bookIds || ! ($year = ManualAllocation::currentYear())) {
            return [];
        }

        $rows = fn () => ManualAllocation::query()->where('academic_year', $year)->whereIn('manual_book_id', $bookIds);

        if ($zoneId && (! $from || (int) $from->zone_id !== $zoneId) && ($zoneRows = $rows()->where('zone_id', $zoneId)->get(['manual_book_id', 'edition', 'quantity']))->isNotEmpty()) {
            return $this->allocationHints('Zone', $zoneRows, ManualAllocation::inboundZoneProgress([$zoneId], $bookIds), $zoneId);
        }

        if (! $regionId || ($from && (int) $from->region_id === $regionId)) {
            return [];
        }

        $regionRows = $rows()->where('region_id', $regionId)->whereNull('zone_id')->get(['manual_book_id', 'edition', 'quantity']);

        return $this->allocationHints('Region', $regionRows, ManualAllocation::inboundProgress([$regionId], $bookIds), $regionId);
    }

    private function allocationHints(string $level, $rows, $sent, int $placeId): array
    {
        return $rows
            ->groupBy('manual_book_id')
            ->map(fn ($editions, $bookId) => [
                'level' => $level,
                'student' => (int) $editions->where('edition', ManualBook::EDITION_STUDENT)->sum('quantity'),
                'teacher' => (int) $editions->where('edition', ManualBook::EDITION_TEACHER)->sum('quantity'),
                'sent_student' => (int) ($sent["{$placeId}:{$bookId}"]->sent_student ?? 0),
                'sent_teacher' => (int) ($sent["{$placeId}:{$bookId}"]->sent_teacher ?? 0),
            ])
            ->all();
    }

    /**
     * Titles that may go on this shipment: the user's own (printers and hubs see every title),
     * narrowed to those used in the destination's region once one is chosen.
     */
    private function titles()
    {
        return ManualBook::query()
            ->when(! $this->sendsAnywhere(), fn ($query) => $query->accessibleBy(Auth::user()))
            ->forRegion($this->titleRegion()['id'] ?? null);
    }

    /**
     * The region the titles are listed for: the destination's once one is chosen, since the
     * books must suit where they go, otherwise the region chosen for the source.
     *
     * @return array{id: int, side: string}|null
     */
    private function titleRegion(): ?array
    {
        if ($regionId = $this->destinationRegionId()) {
            return ['id' => $regionId, 'side' => 'destination'];
        }

        // A regional source (not a printer or hub that sends anywhere) holds only its region's titles
        if (! $this->sendsAnywhere() && ($regionId = $this->sourceRegionId())) {
            return ['id' => $regionId, 'side' => 'source'];
        }

        return filled($this->source['region_id'] ?? null) ? ['id' => (int) $this->source['region_id'], 'side' => 'source'] : null;
    }

    private function sourceRegionId(): ?int
    {
        $regionId = $this->from_organization_id
            ? Organization::query()->whereKey($this->from_organization_id)->value('region_id')
            : null;

        return $regionId ? (int) $regionId : null;
    }

    private function destinationRegionId(): ?int
    {
        $to = $this->to_organization_id ? Organization::query()->find($this->to_organization_id, ['id', 'region_id']) : null;
        $regionId = $to?->region_id ?: $this->destination['region_id'];

        return $regionId ? (int) $regionId : null;
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
