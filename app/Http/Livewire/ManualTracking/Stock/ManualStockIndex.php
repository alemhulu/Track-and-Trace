<?php

namespace App\Http\Livewire\ManualTracking\Stock;

use App\Http\Livewire\Concerns\AuthorizesPermissions;
use App\Models\ManualTracking\ManualAllocation;
use App\Models\ManualTracking\ManualBook;
use App\Models\ManualTracking\ManualDistribution;
use App\Models\ManualTracking\ManualDistributionLine;
use App\Models\ManualTracking\ManualStockBalance;
use App\Models\Organization;
use App\Models\User;
use App\Services\ManualTracking\ManualStockService;
use App\Services\ManualTracking\ManualTrackingException;
use App\Support\ScopedLocation;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class ManualStockIndex extends Component
{
    use WithPagination;
    use AuthorizesPermissions;

    public $area = ['region_id' => '', 'zone_id' => '', 'woreda_id' => ''];
    public $organizationFilter = '';
    public $bookFilter = '';
    public $search = '';
    // '' | on_hand | in_transit: rows holding copies, or rows with copies on the road
    public $holding = '';
    public $recordes = 10;

    // Opening stock form; its own region, zone and woreda narrow the locations offered
    public $showOpeningForm = false;
    public $openingArea = ['region_id' => '', 'zone_id' => '', 'woreda_id' => ''];
    public $opening = [
        'organization_id' => '',
        'manual_book_id' => '',
        'full_packages' => 0,
        'partial_package_books' => 0,
        'partial_teacher_guides' => 0,
        'extra_teacher_guides' => 0,
        'notes' => '',
    ];

    public function mount()
    {
        $this->area = $this->location()->apply([]);
        $this->organizationFilter = $this->location()->organization($this->area, '');
        $this->openingArea = $this->location()->apply([]);
    }

    public function updatedArea($value, $key)
    {
        $this->area = $this->location()->apply($this->area, $key);
        $this->updatedOrganizationFilter();

        // A title of another region no longer fits the chosen area
        if ($this->bookFilter && ! $this->titlesFor($this->area['region_id'] ?? '')->whereKey($this->bookFilter)->exists()) {
            $this->bookFilter = '';
        }
    }

    public function updatedOpeningArea($value, $key)
    {
        $this->openingArea = $this->location()->apply($this->openingArea, $key);
        $this->opening['organization_id'] = $this->validOpeningHolder($this->opening['organization_id']);
        $this->updatedOpeningOrganizationId();
    }

    // Choosing a location fills in its region and zone, and drops a title of another region
    public function updatedOpeningOrganizationId()
    {
        $this->opening['organization_id'] = $this->opening['organization_id'] && $this->openingHolders([])->whereKey($this->opening['organization_id'])->exists()
            ? (string) $this->opening['organization_id']
            : '';
        $holder = $this->openingHolder();

        if ($holder) {
            $this->openingArea = $this->location()->apply(array_merge($this->openingArea, [
                'region_id' => (string) $holder->region_id,
                'zone_id' => (string) ($holder->zone_id ?? ''),
                'woreda_id' => (string) ($holder->woreda_id ?? ''),
            ]));
        }

        if ($this->opening['manual_book_id'] && ! $this->openingTitles()->whereKey($this->opening['manual_book_id'])->exists()) {
            $this->opening['manual_book_id'] = '';
        }
    }


    public function updatingOrganizationFilter()
    {
        $this->resetPage();
    }

    public function updatedOrganizationFilter()
    {
        $this->organizationFilter = $this->location()->organization($this->area, $this->organizationFilter);
        $this->resetPage();
    }

    public function updatingBookFilter()
    {
        $this->resetPage();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingHolding()
    {
        $this->resetPage();
    }

    public function setHolding(string $holding): void
    {
        $this->holding = $this->holding === $holding || ! in_array($holding, ['on_hand', 'in_transit'], true) ? '' : $holding;
        $this->resetPage();
    }

    public function updatingRecordes()
    {
        $this->resetPage();
    }

    public function render()
    {
        /** @var User $actor */
        $actor = Auth::user();

        $filtered = fn () => ManualStockBalance::query()
            ->accessibleBy($actor)
            ->whereIn('organization_id', $this->location()->organizations($this->area)->select('id'))
            ->when($this->organizationFilter, fn ($query) => $query->where('organization_id', $this->organizationFilter))
            ->when($this->bookFilter, fn ($query) => $query->where('manual_book_id', $this->bookFilter))
            ->when(trim($this->search) !== '', fn ($query) => $query->where(fn ($match) => $match
                ->whereHas('organization', fn ($organization) => $organization->where('name', 'like', '%' . trim($this->search) . '%'))
                ->orWhereHas('book', fn ($book) => $book->where('title', 'like', '%' . trim($this->search) . '%'))));

        $balances = $filtered()
            ->selectRaw("organization_id, manual_book_id,
                SUM(CASE WHEN edition = 'student' THEN on_hand ELSE 0 END) as student_on_hand,
                SUM(CASE WHEN edition = 'teacher' THEN on_hand ELSE 0 END) as teacher_on_hand,
                SUM(CASE WHEN edition = 'student' THEN in_transit_out ELSE 0 END) as student_in_transit,
                SUM(CASE WHEN edition = 'teacher' THEN in_transit_out ELSE 0 END) as teacher_in_transit")
            ->groupBy('organization_id', 'manual_book_id')
            ->havingRaw('SUM(on_hand) + SUM(in_transit_out) > 0')
            ->when($this->holding === 'on_hand', fn ($query) => $query->havingRaw('SUM(on_hand) > 0'))
            ->when($this->holding === 'in_transit', fn ($query) => $query->havingRaw('SUM(in_transit_out) > 0'))
            ->with(['organization:id,name', 'book:id,title,students_per_package'])
            ->orderBy('organization_id')
            ->orderBy('manual_book_id')
            ->paginate($this->recordes);

        return view('livewire.manual-tracking.stock.manual-stock-index', [
            'balances' => $balances,
            'locationPicker' => $this->location()->picker($this->area, null, $this->organizationFilter),
            'books' => $this->titlesFor($this->area['region_id'] ?? '')->orderBy('title')->get(['id', 'title']),
            'openingPicker' => $this->location()->picker($this->openingArea, $this->openingHolders($this->openingArea), $this->opening['organization_id'] ?? null),
            'openingBooks' => $this->openingTitles()->orderBy('title')->get(['id', 'title']),
            'openingStanding' => $standing = $this->openingStanding(),
            'openingRoom' => $this->openingRoom($standing),
            'canRecordOpening' => $this->canRecordOpening($actor),
            'openingCopies' => $this->openingCopies(),
            'stats' => $this->stats($filtered),
        ])->extends('main.manual-tracking.index');
    }

    /**
     * Totals over every matching location and title, plus copies on their way in.
     */
    private function stats(\Closure $filtered): array
    {
        $totals = $filtered()
            ->join('manual_books', 'manual_books.id', '=', 'manual_stock_balances.manual_book_id')
            ->selectRaw("SUM(CASE WHEN manual_stock_balances.edition = 'student' THEN manual_stock_balances.on_hand ELSE 0 END) as student")
            ->selectRaw("SUM(CASE WHEN manual_stock_balances.edition = 'teacher' THEN manual_stock_balances.on_hand ELSE 0 END) as teacher")
            ->selectRaw("SUM(CASE WHEN manual_stock_balances.edition = 'student' THEN manual_stock_balances.in_transit_out ELSE 0 END) as student_moving")
            ->selectRaw("SUM(CASE WHEN manual_stock_balances.edition = 'teacher' THEN manual_stock_balances.in_transit_out ELSE 0 END) as teacher_moving")
            ->selectRaw("SUM(CASE WHEN manual_stock_balances.edition = 'student' THEN FLOOR(manual_stock_balances.on_hand / GREATEST(manual_books.students_per_package, 1)) ELSE 0 END) as packages")
            ->selectRaw('COUNT(DISTINCT CASE WHEN manual_stock_balances.on_hand > 0 THEN manual_stock_balances.organization_id END) as locations')
            ->selectRaw('COUNT(DISTINCT CASE WHEN manual_stock_balances.on_hand > 0 THEN manual_stock_balances.manual_book_id END) as titles')
            ->selectRaw('COUNT(DISTINCT CASE WHEN manual_stock_balances.in_transit_out > 0 THEN manual_stock_balances.organization_id END) as sending')
            ->first();

        // Shipments still on the road to the places shown
        $incoming = ManualDistributionLine::query()
            ->whereHas('distribution', fn ($shipment) => $shipment
                ->accessibleBy(Auth::user())
                ->where('status', ManualDistribution::STATUS_DISPATCHED)
                ->whereIn('destination_organization_id', $this->organizationFilter
                    ? [(int) $this->organizationFilter]
                    : $this->location()->organizations($this->area)->select('id')))
            ->when($this->bookFilter, fn ($query) => $query->where('manual_book_id', $this->bookFilter))
            ->selectRaw('COALESCE(SUM(quantity), 0) as student, COALESCE(SUM(teacher_guides), 0) as teacher, COUNT(DISTINCT manual_distribution_id) as shipments')
            ->first();

        return [
            'student' => (int) ($totals->student ?? 0),
            'teacher' => (int) ($totals->teacher ?? 0),
            'student_moving' => (int) ($totals->student_moving ?? 0),
            'teacher_moving' => (int) ($totals->teacher_moving ?? 0),
            'packages' => (int) ($totals->packages ?? 0),
            'locations' => (int) ($totals->locations ?? 0),
            'titles' => (int) ($totals->titles ?? 0),
            'sending' => (int) ($totals->sending ?? 0),
            'incoming_student' => (int) ($incoming->student ?? 0),
            'incoming_teacher' => (int) ($incoming->teacher ?? 0),
            'incoming_shipments' => (int) ($incoming->shipments ?? 0),
        ];
    }

    public function recordOpeningStock(ManualStockService $stock)
    {
        abort_unless($this->canRecordOpening(Auth::user()), 403);

        $this->validate([
            'opening.organization_id' => 'required|exists:organizations,id',
            'opening.manual_book_id' => 'required|exists:manual_books,id',
            'opening.full_packages' => 'required|integer|min:0',
            'opening.partial_package_books' => 'required|integer|min:0',
            'opening.partial_teacher_guides' => 'required|integer|min:0',
            'opening.extra_teacher_guides' => 'required|integer|min:0',
            'opening.notes' => 'nullable|string|max:500',
        ], [], [
            'opening.organization_id' => 'organization',
            'opening.manual_book_id' => 'title',
        ]);

        $holder = Organization::query()->findOrFail($this->opening['organization_id']);
        $book = ManualBook::query()->accessibleBy(Auth::user())->forRegion($holder->region_id)->find($this->opening['manual_book_id']);
        if (! $book) {
            return $this->alert('error', 'That title is not used in the location\'s region.');
        }

        // Opening stock may not take a region or zone past its allocation
        if ($over = collect($this->openingStanding())->first(fn ($row) => $row['over_student'] + $row['over_teacher'] > 0)) {
            return $this->alert('error', sprintf('This would put %s %s over its allocation (%s SB, %s TG). Record no more than %s SB and %s TG.',
                strtolower($over['level']), $over['name'],
                number_format($over['over_student']), number_format($over['over_teacher']),
                number_format(max(0, $over['room_student'])), number_format(max(0, $over['room_teacher']))));
        }

        try {
            $stock->recordOpeningStock(
                Auth::user(),
                $holder,
                $book,
                $this->openingCopies(),
                $this->opening['notes'] ?: null
            );
        } catch (ManualTrackingException $exception) {
            return $this->alert('error', $exception->getMessage());
        }

        $this->reset('opening', 'showOpeningForm');
        $this->openingArea = $this->location()->apply([]);
        $this->alert('success', 'Opening stock recorded.');
    }

    /**
     * Fill the form with everything the allocation still allows at the chosen location, packed
     * as full and partial packages plus extra teacher guides.
     */
    public function fillAllowed(): void
    {
        $room = $this->openingRoom($this->openingStanding());
        $book = $this->opening['manual_book_id'] ? ManualBook::query()->find($this->opening['manual_book_id']) : null;

        if ($room && $book) {
            $this->opening = array_merge($this->opening, $book->packagesFor($room['student'], $room['teacher']));
        }
    }

    /**
     * What the allocation still allows: the smaller room of the region and the zone, per edition.
     *
     * @return array{student: int, teacher: int, packing: array<string, int>}|null
     */
    private function openingRoom(array $standing): ?array
    {
        $book = $this->opening['manual_book_id'] ? ManualBook::query()->find($this->opening['manual_book_id']) : null;
        if (! $standing || ! $book) {
            return null;
        }

        $room = [
            'student' => max(0, (int) collect($standing)->min('room_student')),
            'teacher' => $book->has_teacher_guide ? max(0, (int) collect($standing)->min('room_teacher')) : 0,
        ];

        return $room + ['packing' => $book->packagesFor($room['student'], $room['teacher'])];
    }

    // Titles the user can see that are used in the region; no region keeps them all
    private function titlesFor($regionId)
    {
        return ManualBook::query()->accessibleBy(Auth::user())->forRegion($regionId);
    }

    private function openingHolder(): ?Organization
    {
        return $this->opening['organization_id']
            ? $this->openingHolders([])->find($this->opening['organization_id'], ['id', 'region_id', 'zone_id', 'woreda_id'])
            : null;
    }

    // Schools only receive by shipment, so they are never offered for opening stock
    private function openingHolders(array $area)
    {
        return $this->location()->organizations($area)
            ->whereDoesntHave('organizationType', fn ($type) => $type->where('name', 'School'));
    }

    private function validOpeningHolder($organizationId): string
    {
        $id = $this->location()->organization($this->openingArea, $organizationId);

        return $id !== '' && $this->openingHolders($this->openingArea)->whereKey($id)->exists() ? $id : '';
    }

    private function openingTitles()
    {
        return $this->titlesFor($this->openingHolder()?->region_id ?? ($this->openingArea['region_id'] ?? ''));
    }

    /**
     * The chosen title's allocation where the chosen location is, against what is already
     * held there and what this entry would add.
     */
    private function openingStanding(): array
    {
        $holder = $this->openingHolder();
        $regionId = (int) ($holder?->region_id ?: ($this->openingArea['region_id'] ?? 0));
        $zoneId = (int) ($holder?->zone_id ?: ($this->openingArea['zone_id'] ?? 0));

        if (! $this->opening['manual_book_id'] || ! $regionId) {
            return [];
        }

        $adding = $this->openingCopies();
        $names = [
            'Region' => \App\Models\Region::query()->whereKey($regionId)->value('name'),
            'Zone' => $zoneId ? \App\Models\Zone::query()->whereKey($zoneId)->value('name') : null,
        ];

        return collect(ManualAllocation::standing((int) $this->opening['manual_book_id'], $regionId, $zoneId ?: null))
            ->map(fn ($row) => $row + [
                'name' => $names[$row['level']] ?? '',
                'room_student' => $row['student'] - $row['held_student'],
                'room_teacher' => $row['teacher'] - $row['held_teacher'],
                'over_student' => max(0, $row['held_student'] + $adding[ManualBook::EDITION_STUDENT] - $row['student']),
                'over_teacher' => max(0, $row['held_teacher'] + $adding[ManualBook::EDITION_TEACHER] - $row['teacher']),
            ])
            ->all();
    }

    /**
     * Opening balances need the stock permission and national, admin or regional reach.
     */
    private function canRecordOpening(User $actor): bool
    {
        return $actor->can('manual-tracking.stock.manage')
            && ($actor->hasNationalAccess() || $actor->hasRole('Admin')
                || $actor->effectiveAccessLevel() === User::ACCESS_LEVEL_REGION);
    }

    /**
     * @return array{student: int, teacher: int}
     */
    private function openingCopies(): array
    {
        $book = $this->opening['manual_book_id'] ? ManualBook::query()->find($this->opening['manual_book_id']) : null;

        return $book
            ? $book->copiesFor((int) $this->opening['full_packages'], (int) $this->opening['partial_package_books'], (int) $this->opening['partial_teacher_guides'], (int) $this->opening['extra_teacher_guides'])
            : [ManualBook::EDITION_STUDENT => 0, ManualBook::EDITION_TEACHER => 0];
    }

    private function alert(string $type, string $message): void
    {
        $this->dispatchBrowserEvent('alert', ['type' => $type, 'message' => $message]);
    }

    private function location(): ScopedLocation
    {
        return ScopedLocation::for(Auth::user());
    }

    protected function permissionMap(): array
    {
        return [
            'view' => 'manual-tracking.view',
            'actions' => [
                'recordOpeningStock' => 'manual-tracking.stock.manage',
            ],
        ];
    }
}
