<?php

namespace Tests\Feature;

use App\Http\Livewire\ManualTracking\Allocation\AllocationIndex;
use App\Http\Livewire\ManualTracking\Book\AddManualBook;
use App\Http\Livewire\ManualTracking\Book\ListManualBook;
use App\Http\Livewire\ManualTracking\Discrepancy\ManualDiscrepancyIndex;
use App\Http\Livewire\ManualTracking\Distribution\ListManualDistribution;
use App\Http\Livewire\ManualTracking\Oversight\VisitIndex;
use App\Http\Livewire\ManualTracking\Scan\ScanAlertIndex;
use App\Http\Livewire\ManualTracking\Shipment\CreateShipment;
use App\Http\Livewire\ManualTracking\Stock\ManualStockIndex;
use App\Http\Livewire\School\SchoolIndex;
use App\Models\AcademicYear;
use App\Models\ManualTracking\ManualAllocation;
use App\Models\ManualTracking\ManualBook;
use App\Models\ManualTracking\ManualDiscrepancy;
use App\Models\ManualTracking\ManualDistribution;
use App\Models\ManualTracking\ManualStockBalance;
use App\Models\Organization;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\Showcase\ShowcaseData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * The search box and "Show N entries" above the manual tracking tables, and the small
 * workflow fixes found while walking the module with each kind of user.
 */
class ManualTrackingTableControlsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $_ENV['SEED_PROFILE'] = 'showcase';
        $this->seed(DatabaseSeeder::class);
        unset($_ENV['SEED_PROFILE']);
    }

    public function test_search_and_page_size_work_on_every_list(): void
    {
        $this->actingAs($this->user('moe.officer'));

        foreach ([ListManualBook::class, ManualStockIndex::class, ManualDiscrepancyIndex::class, ListManualDistribution::class, SchoolIndex::class] as $component) {
            Livewire::test($component)
                ->set('search', 'Oromia')
                ->set('recordes', 25)
                ->assertOk()
                ->assertHasNoErrors();
        }
    }

    public function test_title_search_narrows_the_list(): void
    {
        $this->actingAs($this->user('moe.officer'));
        [$wanted, $other] = ManualBook::query()->orderBy('title')->take(2)->get()->all();

        Livewire::test(ListManualBook::class)
            ->set('search', $wanted->title)
            ->assertSee($wanted->title)
            ->assertDontSee($other->title);
    }

    public function test_field_errors_raise_a_readable_toast(): void
    {
        $this->actingAs(User::query()->where('email', 'superadmin@gmail.com')->firstOrFail());

        Livewire::test(AddManualBook::class)
            ->call('saveBook')
            ->assertHasErrors(['region_id'])
            ->assertDispatchedBrowserEvent('alert', fn ($event, $data) => $data['message'] === 'The region field is required.');
    }

    public function test_editing_an_unknown_title_is_not_found(): void
    {
        $this->actingAs(User::query()->where('email', 'superadmin@gmail.com')->firstOrFail());

        $this->get(route('manual-tracking.books.add', ['edit' => 999999]))->assertNotFound();
    }

    public function test_schools_are_not_offered_for_opening_stock(): void
    {
        $this->actingAs(User::query()->where('email', 'superadmin@gmail.com')->firstOrFail());
        $school = Organization::query()->whereHas('organizationType', fn ($type) => $type->where('name', 'School'))->firstOrFail();

        $component = Livewire::test(ManualStockIndex::class)
            ->set('opening.organization_id', (string) $school->id)
            ->assertSet('opening.organization_id', '');

        $offered = collect($component->viewData('openingPicker')['organizations'])->pluck('id');
        $this->assertNotContains($school->id, $offered);
    }

    public function test_a_regional_source_lists_only_its_region_s_titles(): void
    {
        $this->actingAs(User::query()->where('email', 'superadmin@gmail.com')->firstOrFail());
        $bureau = Organization::query()
            ->whereNotNull('region_id')->whereNull('zone_id')
            ->whereDoesntHave('organizationType', fn ($type) => $type->whereIn('name', ['School', 'Printer', 'Hub', 'Warehouse']))
            ->get()
            ->first(fn ($org) => ManualBook::query()->where('region_id', '!=', $org->region_id)->exists());
        $this->assertNotNull($bureau, 'The showcase data needs a regional office and a title of another region.');

        $books = Livewire::test(CreateShipment::class)
            ->set('from_organization_id', (string) $bureau->id)
            ->viewData('books');

        $this->assertNotEmpty($books);
        $this->assertTrue($books->keys()->every(fn ($id) => in_array(ManualBook::query()->whereKey($id)->value('region_id'), [null, $bureau->region_id])));
    }

    public function test_a_single_source_is_chosen_for_the_sender(): void
    {
        $officer = User::query()
            ->where('access_level', User::ACCESS_LEVEL_WOREDA)
            ->whereNull('organization_id')
            ->get()
            ->first(fn (User $user) => ! $user->isSchoolUser() && $user->can('manual-tracking.shipments.dispatch'));
        $this->actingAs($officer);

        $office = Organization::query()
            ->where('woreda_id', $officer->woreda_id)
            ->whereDoesntHave('organizationType', fn ($type) => $type->where('name', 'School'))
            ->sole();

        Livewire::test(CreateShipment::class)->assertSet('from_organization_id', (string) $office->id);
    }

    public function test_the_receiver_is_not_offered_a_resolve_form_for_its_own_hand_off(): void
    {
        $discrepancy = ManualDiscrepancy::query()
            ->where('status', ManualDiscrepancy::STATUS_OPEN)
            ->with('line.distribution')
            ->get()
            ->first(fn ($discrepancy) => $discrepancy->line->distribution->received_by);
        $receiver = User::query()->findOrFail($discrepancy->line->distribution->received_by);

        $this->actingAs($receiver);
        if (! $receiver->can('manual-tracking.discrepancies.resolve')) {
            $this->markTestSkipped('The receiver in the showcase data cannot resolve discrepancies at all.');
        }

        Livewire::test(ManualDiscrepancyIndex::class)
            ->assertSee('Awaiting review by someone other than the sender and receiver')
            ->assertDontSeeHtml('wire:click="resolve(' . $discrepancy->id . ')"');
    }

    public function test_list_figures_and_filters_follow_the_data(): void
    {
        $this->actingAs(User::query()->where('email', 'superadmin@gmail.com')->firstOrFail());

        // Shipments: the status tiles add up and filter the table
        $shipments = Livewire::test(ListManualDistribution::class);
        $stats = $shipments->viewData('stats');
        $this->assertSame(ManualDistribution::query()->count(), $stats['total']);
        $this->assertSame($stats['total'], array_sum($stats['status']));
        $shipments->call('filterStatus', ManualDistribution::STATUS_DISPATCHED)
            ->assertSet('statusFilter', ManualDistribution::STATUS_DISPATCHED)
            ->assertViewHas('distributions', fn ($page) => collect($page->items())->every->isInTransit())
            ->call('filterStatus', ManualDistribution::STATUS_DISPATCHED)
            ->assertSet('statusFilter', '')
            ->set('overdueOnly', true)->set('dateFrom', '2000-01-01')->set('direction', 'incoming')
            ->assertOk()
            ->call('clearFilters')
            ->assertSet('overdueOnly', false);

        // Titles: the QR tiles filter on linked codes
        Livewire::test(ListManualBook::class)
            ->call('setFilter', 'codes', 'unlinked')
            ->assertViewHas('books', fn ($page) => collect($page->items())->every(fn ($book) => $book->codes->isEmpty()))
            ->assertViewHas('stats', fn ($stats) => $stats['linked'] === 0)
            ->call('setFilter', 'codes', 'unlinked')
            ->assertSet('filters.codes', '');

        // Stock: rows with copies on the road
        Livewire::test(ManualStockIndex::class)
            ->call('setHolding', 'in_transit')
            ->assertViewHas('balances', fn ($page) => collect($page->items())->every(fn ($row) => $row->student_in_transit + $row->teacher_in_transit > 0))
            ->assertViewHas('stats', fn ($stats) => $stats['student'] >= 0 && $stats['locations'] >= 0);
    }

    public function test_allocation_progress_figures_and_filter(): void
    {
        $this->actingAs(User::query()->where('email', 'superadmin@gmail.com')->firstOrFail());

        $delivered = ManualAllocation::inboundProgress()->first(fn ($row) => $row->received_student > 0);
        $this->assertNotNull($delivered, 'The showcase data should deliver into some region.');
        $waiting = ManualBook::query()->whereKeyNot($delivered->manual_book_id)
            ->whereNotIn('id', ManualAllocation::inboundProgress([$delivered->region_id])->pluck('manual_book_id'))->firstOrFail();

        foreach ([[$delivered->manual_book_id, (int) $delivered->received_student], [$waiting->id, 500]] as $row => [$book, $quantity]) {
            ManualAllocation::query()->forceCreate([
                'academic_year' => '2019', 'region_id' => $delivered->region_id, 'manual_book_id' => $book,
                'edition' => ManualBook::EDITION_STUDENT, 'quantity' => $quantity, 'source_row' => $row + 1,
            ]);
        }
        $deliveredTitle = ManualBook::query()->findOrFail($delivered->manual_book_id)->title;

        Livewire::test(AllocationIndex::class)
            ->set('year', '2019')
            ->assertViewHas('stats', fn ($stats) => $stats['lines'] === 2
                && $stats['progress']['complete'] === 1 && $stats['progress']['not_started'] === 1
                && $stats['student'] === (int) $delivered->received_student + 500
                && $stats['due_student'] === 500)
            ->call('filterProgress', 'complete')
            ->assertSee($deliveredTitle)
            ->assertDontSee($waiting->title)
            ->call('filterProgress', 'complete')
            ->assertSet('progress', '')
            ->set('language', 'No such language')
            ->assertViewHas('stats', fn ($stats) => $stats['lines'] === 0);
    }

    public function test_monitoring_pages_show_figures_and_filter(): void
    {
        $this->actingAs(User::query()->where('email', 'superadmin@gmail.com')->firstOrFail());

        // Discrepancies: open and resolved add up to everything visible, and the type tile filters
        $discrepancies = Livewire::test(ManualDiscrepancyIndex::class)->set('statusFilter', '');
        $stats = $discrepancies->viewData('stats');
        $this->assertSame(ManualDiscrepancy::query()->count(), $stats['open'] + $stats['resolved']);
        $discrepancies->call('setFilter', 'typeFilter', ManualDiscrepancy::TYPE_MISSING)
            ->assertViewHas('discrepancies', fn ($page) => collect($page->items())->every(fn ($row) => $row->type === ManualDiscrepancy::TYPE_MISSING))
            ->set('dateFrom', '2000-01-01')->set('editionFilter', ManualBook::EDITION_STUDENT)->set('search', 'SH-')
            ->assertOk()
            ->call('clearFilters')
            ->assertSet('typeFilter', '');

        // Misroutes
        Livewire::test(ScanAlertIndex::class)
            ->assertViewHas('stats', fn ($stats) => $stats['open'] >= 0 && array_key_exists('misroute_zone', $stats['flags']))
            ->call('setFilter', 'flagFilter', 'misroute_zone')
            ->assertViewHas('alerts', fn ($page) => collect($page->items())->every(fn ($alert) => $alert->flag === 'misroute_zone'))
            ->set('contextFilter', 'receive')->set('search', 'Zone')
            ->assertOk();

        // Schools: the state tiles narrow the list to schools in that state
        $schools = Livewire::test(SchoolIndex::class);
        $stats = $schools->viewData('stats');
        $this->assertGreaterThan(0, $stats['schools']);
        $schools->call('setState', 'with_stock')
            ->assertViewHas('schools', fn ($page) => $page->total() === $stats['states']['with_stock'])
            ->call('setState', 'with_stock')
            ->assertSet('state', '');

        // Visits
        Livewire::test(VisitIndex::class)
            ->assertViewHas('stats', fn ($stats) => $stats['results']['clean'] <= $stats['visits'] && $stats['schools'] <= $stats['school_total'])
            ->call('setResult', 'shelf')
            ->assertViewHas('visits', fn ($page) => collect($page->items())->every(fn ($visit) => $visit->stock_lines_matching < $visit->stock_lines))
            ->set('search', 'School')->set('dateTo', '2100-01-01')
            ->assertOk();
    }

    public function test_titles_follow_the_chosen_region_and_opening_stock_shows_the_allocation(): void
    {
        $this->actingAs(User::query()->where('email', 'superadmin@gmail.com')->firstOrFail());

        $ours = ManualBook::query()->whereNotNull('region_id')->firstOrFail();
        $theirs = ManualBook::query()->whereNotNull('region_id')->where('region_id', '!=', $ours->region_id)->firstOrFail();
        $holder = Organization::query()->where('region_id', $ours->region_id)->whereNotNull('zone_id')->firstOrFail();
        $ids = fn ($books) => $books->pluck('id')->all();

        // Stock filter: a region shows its own titles and those used everywhere
        Livewire::test(ManualStockIndex::class)
            ->set('bookFilter', (string) $theirs->id)
            ->set('area.region_id', (string) $ours->region_id)
            ->assertSet('bookFilter', '')
            ->assertViewHas('books', fn ($books) => in_array($ours->id, $ids($books)) && ! in_array($theirs->id, $ids($books)));

        // Opening stock: the location sets the region, the titles follow it, and the allocation is shown
        AcademicYear::ensure(2099, current: true);
        // Room for exactly 10 more student books on top of what the region already holds
        $allocation = ManualAllocation::query()->forceCreate([
            'academic_year' => '2099', 'region_id' => $ours->region_id, 'manual_book_id' => $ours->id,
            'edition' => ManualBook::EDITION_STUDENT, 'quantity' => 0,
        ]);
        $allowed = ManualAllocation::standing($ours->id, $ours->region_id, null)[0]['held_student'] + 10;
        $allocation->forceFill(['quantity' => $allowed])->save();

        Livewire::test(ManualStockIndex::class)
            ->set('showOpeningForm', true)
            ->set('opening.organization_id', (string) $holder->id)
            ->assertSet('openingArea.region_id', (string) $ours->region_id)
            ->assertViewHas('openingBooks', fn ($books) => ! in_array($theirs->id, $ids($books)))
            ->set('opening.manual_book_id', (string) $ours->id)
            ->set('opening.full_packages', 1)
            ->assertViewHas('openingStanding', fn ($standing) => $standing[0]['level'] === 'Region'
                && $standing[0]['student'] === $allowed && $standing[0]['over_student'] > 0)
            ->assertSee('over its allocation')
            ->call('recordOpeningStock')
            ->assertDispatchedBrowserEvent('alert', fn ($event, $data) => $data['type'] === 'error' && str_contains($data['message'], 'over its allocation'))
            ->assertSet('showOpeningForm', true)
            ->set('opening.manual_book_id', (string) $theirs->id)
            ->call('recordOpeningStock')
            ->assertDispatchedBrowserEvent('alert', fn ($event, $data) => $data['type'] === 'error' && str_contains($data['message'], 'region'))
            ->set('opening.manual_book_id', (string) $ours->id)
            ->set('opening.full_packages', 0)
            ->set('opening.partial_package_books', 10)
            ->assertViewHas('openingStanding', fn ($standing) => $standing[0]['over_student'] === 0)
            ->call('recordOpeningStock')
            ->assertDispatchedBrowserEvent('alert', fn ($event, $data) => $data['type'] === 'success');

        // New Shipment: titles follow the destination region, and a title of another region is cleared
        Livewire::test(CreateShipment::class)
            ->set('lines.0.manual_book_id', (string) $theirs->id)
            ->set('destination.region_id', (string) $ours->region_id)
            ->assertSet('lines.0.manual_book_id', '')
            ->assertViewHas('books', fn ($books) => $books->has($ours->id) && ! $books->has($theirs->id));;

        // Before a destination is chosen, the source region narrows the titles
        Livewire::test(CreateShipment::class)
            ->set('destination.region_id', '')
            ->set('to_organization_id', '')
            ->set('lines.0.manual_book_id', (string) $theirs->id)
            ->set('source.region_id', (string) $ours->region_id)
            ->assertSet('lines.0.manual_book_id', '')
            ->assertViewHas('books', fn ($books) => $books->has($ours->id) && ! $books->has($theirs->id))
            ->assertSee('the source region');
    }

    public function test_fill_allowed_packs_what_the_allocation_still_allows(): void
    {
        $this->actingAs(User::query()->where('email', 'superadmin@gmail.com')->firstOrFail());

        AcademicYear::ensure(2099, current: true);

        // Opening stock: room for 405 student books and 20 teacher guides on top of what the region holds
        $book = ManualBook::query()->whereNotNull('region_id')->where('has_teacher_guide', true)->where('students_per_package', 40)->firstOrFail();
        $holder = Organization::query()->where('region_id', $book->region_id)->firstOrFail();
        $rows = collect([ManualBook::EDITION_STUDENT, ManualBook::EDITION_TEACHER])->mapWithKeys(fn ($edition) => [$edition => ManualAllocation::query()->forceCreate([
            'academic_year' => '2099', 'region_id' => $book->region_id, 'manual_book_id' => $book->id, 'edition' => $edition, 'quantity' => 0,
        ])]);
        $held = ManualAllocation::standing($book->id, $book->region_id, null)[0];
        $rows['student']->forceFill(['quantity' => $held['held_student'] + 405])->save();
        $rows['teacher']->forceFill(['quantity' => $held['held_teacher'] + 20])->save();

        Livewire::test(ManualStockIndex::class)
            ->set('showOpeningForm', true)
            ->set('opening.organization_id', (string) $holder->id)
            ->set('opening.manual_book_id', (string) $book->id)
            ->assertSee('You can still stock')
            ->call('fillAllowed')
            ->assertSet('opening.full_packages', 10)
            ->assertSet('opening.partial_package_books', 5)
            ->assertSet('opening.partial_teacher_guides', 1)
            ->assertSet('opening.extra_teacher_guides', 9)
            ->assertViewHas('openingCopies', ['student' => 405, 'teacher' => 20])
            ->call('recordOpeningStock')
            ->assertDispatchedBrowserEvent('alert', fn ($event, $data) => $data['type'] === 'success');

        $after = ManualAllocation::standing($book->id, $book->region_id, null)[0];
        $this->assertSame([0, 0], [$after['student'] - $after['held_student'], $after['teacher'] - $after['held_teacher']]);

        // New Shipment: a zone with more allocation than the source holds is filled up to the source's stock
        $source = Organization::query()->where('region_id', $book->region_id)
            ->whereIn('id', ManualStockBalance::query()->where('manual_book_id', $book->id)->where('edition', 'student')->where('on_hand', '>', 0)->select('organization_id'))
            ->firstOrFail();
        $destination = Organization::query()->where('region_id', $book->region_id)->whereNotNull('zone_id')
            ->where('zone_id', '!=', (int) $source->zone_id)->whereKeyNot($source->id)->firstOrFail();
        foreach ([ManualBook::EDITION_STUDENT, ManualBook::EDITION_TEACHER] as $edition) {
            ManualAllocation::query()->forceCreate([
                'academic_year' => '2099', 'region_id' => $book->region_id, 'zone_id' => $destination->zone_id,
                'manual_book_id' => $book->id, 'edition' => $edition, 'quantity' => 10_000_000,
            ]);
        }
        $onHand = ManualStockBalance::query()->where('organization_id', $source->id)->where('manual_book_id', $book->id)->pluck('on_hand', 'edition');

        Livewire::test(CreateShipment::class)
            ->set('from_organization_id', (string) $source->id)
            ->set('destination.region_id', (string) $destination->region_id)
            ->set('destination.zone_id', (string) $destination->zone_id)
            ->set('to_organization_id', (string) $destination->id)
            ->set('lines.0.manual_book_id', (string) $book->id)
            ->assertSee('Can still send')
            ->call('fillAllowed', 0)
            ->assertViewHas('lineCopies', fn ($copies) => $copies[0] === ['student' => (int) $onHand['student'], 'teacher' => (int) ($onHand['teacher'] ?? 0)]);
    }

    public function test_a_title_cannot_be_on_two_shipment_lines(): void
    {
        $this->actingAs(User::query()->where('email', 'superadmin@gmail.com')->firstOrFail());
        $book = ManualBook::query()->firstOrFail();

        Livewire::test(CreateShipment::class)
            ->set('lines.0.manual_book_id', (string) $book->id)
            ->call('addLine')
            ->set('lines.1.manual_book_id', (string) $book->id)
            ->assertDispatchedBrowserEvent('alert', fn ($event, $data) => $data['type'] === 'warning' && str_contains($data['message'], 'already on line 1'))
            ->assertViewHas('duplicates', [0 => [2], 1 => [1]])
            ->assertSee('Also on line 1')
            ->call('dispatchShipment')
            ->assertDispatchedBrowserEvent('alert', fn ($event, $data) => $data['type'] === 'error' && str_contains($data['message'], 'more than one line'))
            ->call('removeLine', 1)
            ->assertViewHas('duplicates', []);

        $this->assertSame(0, ManualDistribution::query()->where('created_at', '>=', now()->subMinute())->count());
    }

    private function user(string $local): User
    {
        return User::query()->where('email', $local . '@' . ShowcaseData::EMAIL_DOMAIN)->firstOrFail();
    }
}
