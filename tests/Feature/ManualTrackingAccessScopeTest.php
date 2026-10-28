<?php

namespace Tests\Feature;

use App\Http\Livewire\ManualTracking\Book\ListManualBook;
use App\Http\Livewire\ManualTracking\Report\ManualTrackingReports;
use App\Http\Livewire\ManualTracking\Shipment\CreateShipment;
use App\Models\Country;
use App\Models\ManualTracking\ManualBook;
use App\Models\Organization;
use App\Models\OrganizationType;
use App\Models\Region;
use App\Models\User;
use App\Models\Woreda;
use App\Models\Zone;
use App\Services\ManualTracking\ManualShipmentService;
use App\Services\ManualTracking\ManualStockService;
use App\Services\ManualTracking\ManualTrackingException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Users act only inside their own area at every level: shipments, titles, reports and the
 * location lists on the shipment form.
 */
class ManualTrackingAccessScopeTest extends TestCase
{
    use RefreshDatabase;

    private ManualShipmentService $shipments;
    private array $places;
    private array $books;
    private User $national;

    protected function setUp(): void
    {
        parent::setUp();

        $this->shipments = app(ManualShipmentService::class);

        $country = Country::query()->create(['name' => 'Ethiopia', 'code' => 'ET']);
        $oromia = Region::query()->create(['name' => 'Oromia', 'code' => 'OR', 'country_id' => $country->id]);
        $amhara = Region::query()->create(['name' => 'Amhara', 'code' => 'AM', 'country_id' => $country->id]);
        $eastShewa = Zone::query()->create(['name' => 'East Shewa Zone', 'code' => 'ESZ', 'region_id' => $oromia->id, 'country_id' => $country->id]);
        $jimma = Zone::query()->create(['name' => 'Jimma Zone', 'code' => 'JMZ', 'region_id' => $oromia->id, 'country_id' => $country->id]);
        $adama = Woreda::query()->create(['name' => 'Adama Woreda', 'code' => 'ADW', 'zone_id' => $eastShewa->id]);
        $bishoftu = Woreda::query()->create(['name' => 'Bishoftu Woreda', 'code' => 'BSW', 'zone_id' => $eastShewa->id]);

        $org = fn (string $name, array $location) => Organization::query()->create(['name' => $name, 'country_id' => $country->id] + $location);

        $this->places = compact('oromia', 'amhara', 'eastShewa', 'jimma', 'adama', 'bishoftu') + [
            'bureau' => $org('Oromia Education Bureau', ['region_id' => $oromia->id]),
            'amharaBureau' => $org('Amhara Education Bureau', ['region_id' => $amhara->id]),
            'zoneOffice' => $org('East Shewa Zone Education Department', ['region_id' => $oromia->id, 'zone_id' => $eastShewa->id]),
            'jimmaOffice' => $org('Jimma Zone Education Department', ['region_id' => $oromia->id, 'zone_id' => $jimma->id]),
            'woredaOffice' => $org('Adama Woreda Education Office', ['region_id' => $oromia->id, 'zone_id' => $eastShewa->id, 'woreda_id' => $adama->id]),
            'school' => $org('Ifa Bula Primary School', ['region_id' => $oromia->id, 'zone_id' => $eastShewa->id, 'woreda_id' => $adama->id]),
            'otherWoredaSchool' => $org('Bishoftu Primary School', ['region_id' => $oromia->id, 'zone_id' => $eastShewa->id, 'woreda_id' => $bishoftu->id]),
        ];

        $this->books = [
            'oromia' => ManualBook::query()->create(['title' => 'Oromia Grade 6 Mathematics', 'region_id' => $oromia->id, 'students_per_package' => 40]),
            'amhara' => ManualBook::query()->create(['title' => 'Amhara Grade 6 Mathematics', 'region_id' => $amhara->id, 'students_per_package' => 40]),
            'shared' => ManualBook::query()->create(['title' => 'Sign Language Primer', 'students_per_package' => 40]),
        ];

        $this->national = User::factory()->create(['access_level' => User::ACCESS_LEVEL_NATIONAL]);

        $stock = app(ManualStockService::class);
        foreach (['bureau', 'zoneOffice', 'woredaOffice'] as $holder) {
            foreach ($this->books as $book) {
                $stock->recordOpeningStock($this->national, $this->places[$holder], $book, ['student' => 400, 'teacher' => 10]);
            }
        }
    }

    public function test_every_level_can_only_send_inside_its_own_area(): void
    {
        $woreda = $this->woredaUser();
        $this->assertSame($this->places['school']->id, $this->send($woreda, 'woredaOffice', 'school')->destination_organization_id);
        $this->assertRefused($woreda, 'woredaOffice', 'otherWoredaSchool');
        $this->assertRefused($woreda, 'woredaOffice', 'zoneOffice');

        $zone = $this->zoneUser();
        $this->assertSame($this->places['woredaOffice']->id, $this->send($zone, 'zoneOffice', 'woredaOffice')->destination_organization_id);
        $this->assertRefused($zone, 'zoneOffice', 'jimmaOffice');
        $this->assertRefused($zone, 'zoneOffice', 'bureau');

        $region = $this->regionUser();
        $this->assertSame($this->places['jimmaOffice']->id, $this->send($region, 'bureau', 'jimmaOffice')->destination_organization_id);
        $this->assertRefused($region, 'bureau', 'amharaBureau');

        $this->assertSame($this->places['amharaBureau']->id, $this->send($this->national, 'bureau', 'amharaBureau')->destination_organization_id);
    }

    public function test_printers_deliver_outside_their_own_area(): void
    {
        $printer = Organization::query()->create([
            'name' => 'National Printer',
            'country_id' => $this->places['oromia']->country_id,
            'organization_type_id' => OrganizationType::query()->create(['name' => 'Printer'])->id,
        ]);
        $store = User::factory()->create(['access_level' => User::ACCESS_LEVEL_ORGANIZATION, 'organization_id' => $printer->id]);
        $this->places['printer'] = $printer;

        $this->assertSame($this->places['amharaBureau']->id, $this->send($store, 'printer', 'amharaBureau', 'amhara')->destination_organization_id);

        Livewire::actingAs($store)
            ->test(CreateShipment::class)
            ->assertSet('from_organization_id', $printer->id)
            ->assertViewHas('destinationPicker', fn ($picker) => $picker['locked'] === [] && $picker['organizations']->contains('id', $this->places['amharaBureau']->id));
    }

    public function test_titles_from_another_region_cannot_be_sent(): void
    {
        $this->expectException(ManualTrackingException::class);
        $this->expectExceptionMessage('Amhara Grade 6 Mathematics is not a title for your region.');

        $this->send($this->woredaUser(), 'woredaOffice', 'school', 'amhara');
    }

    public function test_shipment_form_lists_only_the_users_area(): void
    {
        Livewire::actingAs($this->woredaUser())
            ->test(CreateShipment::class)
            ->assertSet('destination.region_id', (string) $this->places['oromia']->id)
            ->assertSet('destination.zone_id', (string) $this->places['eastShewa']->id)
            ->assertSet('destination.woreda_id', (string) $this->places['adama']->id)
            ->assertViewHas('destinationPicker', fn ($picker) => $picker['regions']->pluck('id')->all() === [$this->places['oromia']->id]
                && $picker['zones']->pluck('id')->all() === [$this->places['eastShewa']->id]
                && $picker['woredas']->pluck('id')->all() === [$this->places['adama']->id]
                && $this->ids($picker['organizations']) === $this->ids([$this->places['woredaOffice'], $this->places['school']]))
            ->assertViewHas('books', fn ($books) => $books->has($this->books['oromia']->id) && $books->has($this->books['shared']->id) && ! $books->has($this->books['amhara']->id));

        Livewire::actingAs($this->regionUser())
            ->test(CreateShipment::class)
            ->assertSet('destination.zone_id', '')
            ->assertViewHas('destinationPicker', fn ($picker) => $this->ids($picker['zones']) === $this->ids([$this->places['eastShewa'], $this->places['jimma']])
                && ! $picker['organizations']->contains('id', $this->places['amharaBureau']->id));
    }

    public function test_each_access_level_starts_with_its_own_location_locked(): void
    {
        $oromia = (string) $this->places['oromia']->id;
        $eastShewa = (string) $this->places['eastShewa']->id;
        $adama = (string) $this->places['adama']->id;
        $officer = User::factory()->create(['access_level' => User::ACCESS_LEVEL_ORGANIZATION, 'organization_id' => $this->places['woredaOffice']->id]);

        $cases = [
            [$this->national, ['region_id' => '', 'zone_id' => '', 'woreda_id' => ''], []],
            [$this->regionUser(), ['region_id' => $oromia, 'zone_id' => '', 'woreda_id' => ''], ['region_id']],
            [$this->zoneUser(), ['region_id' => $oromia, 'zone_id' => $eastShewa, 'woreda_id' => ''], ['region_id', 'zone_id']],
            [$this->woredaUser(), ['region_id' => $oromia, 'zone_id' => $eastShewa, 'woreda_id' => $adama], ['region_id', 'zone_id', 'woreda_id']],
            [$officer, ['region_id' => $oromia, 'zone_id' => $eastShewa, 'woreda_id' => $adama], ['region_id', 'zone_id', 'woreda_id', 'organization_id']],
        ];

        foreach ($cases as [$user, $area, $locked]) {
            Livewire::actingAs($user)
                ->test(CreateShipment::class)
                ->assertSet('source', $area)
                ->assertViewHas('sourcePicker', fn ($picker) => array_keys($picker['locked']) === $locked);
        }

        Livewire::actingAs($officer)
            ->test(CreateShipment::class)
            ->assertSet('from_organization_id', (string) $this->places['woredaOffice']->id);
    }

    public function test_locked_levels_cannot_be_changed_from_the_browser(): void
    {
        Livewire::actingAs($this->zoneUser())
            ->test(CreateShipment::class)
            ->set('source.region_id', (string) $this->places['amhara']->id)
            ->assertSet('source.region_id', (string) $this->places['oromia']->id)
            ->set('destination.zone_id', (string) $this->places['jimma']->id)
            ->assertSet('destination.zone_id', (string) $this->places['eastShewa']->id)
            ->set('destination.woreda_id', (string) $this->places['adama']->id)
            ->assertSet('destination.woreda_id', (string) $this->places['adama']->id)
            ->set('to_organization_id', (string) $this->places['jimmaOffice']->id)
            ->assertSet('to_organization_id', '');
    }

    public function test_schools_only_receive_shipments(): void
    {
        $schoolType = OrganizationType::query()->create(['name' => 'School']);
        $this->places['school']->update(['organization_type_id' => $schoolType->id]);
        $director = User::factory()->create(['access_level' => User::ACCESS_LEVEL_ORGANIZATION, 'organization_id' => $this->places['school']->id]);

        $this->assertTrue($director->isSchoolUser());
        $this->actingAs($director)->get(route('manual-tracking.distribution.add'))->assertForbidden();
        $this->actingAs($director)->get(route('manual-tracking.scan.sort'))->assertForbidden();

        $shipment = $this->send($this->woredaUser(), 'woredaOffice', 'school');
        $line = $shipment->lines->first();
        $received = $this->shipments->receive($director, $shipment, [$line->id => ['full_packages' => 1, 'partial_package_books' => 0, 'partial_teacher_guides' => 0]]);
        $this->assertSame('received', $received->status);

        // Nobody, the woreda included, can send from a school
        Livewire::actingAs($this->woredaUser())
            ->test(CreateShipment::class)
            ->assertViewHas('sourcePicker', fn ($picker) => $this->ids($picker['organizations']) === $this->ids([$this->places['woredaOffice']]))
            ->set('from_organization_id', (string) $this->places['school']->id)
            ->assertSet('from_organization_id', '');

        $this->expectException(ManualTrackingException::class);
        $this->expectExceptionMessage('Schools receive shipments; they do not send them.');
        $this->send($this->woredaUser(), 'school', 'woredaOffice');
    }

    public function test_only_admins_manage_titles_and_users_see_their_regions_titles(): void
    {
        $woreda = $this->woredaUser();
        $this->actingAs($woreda)->get(route('manual-tracking.books.add'))->assertForbidden();

        Livewire::actingAs($woreda)
            ->test(ListManualBook::class)
            ->assertSee('Oromia Grade 6 Mathematics')
            ->assertSee('Sign Language Primer')
            ->assertDontSee('Amhara Grade 6 Mathematics')
            ->assertDontSee('Add Title')
            ->call('deleteBook', $this->books['shared']->id);
        $this->assertModelExists($this->books['shared']);

        // National access alone is not enough to change titles
        $this->actingAs($this->national)->get(route('manual-tracking.books.add'))->assertForbidden();

        Role::query()->create(['name' => 'Admin', 'guard_name' => 'web']);
        $admin = User::factory()->create(['access_level' => User::ACCESS_LEVEL_NATIONAL]);
        $admin->assignRole('Admin');

        $this->actingAs($admin)->get(route('manual-tracking.books.add'))->assertOk();
        Livewire::actingAs($admin)->test(ListManualBook::class)->assertSee('Amhara Grade 6 Mathematics')->assertSee('Add Title');
    }

    public function test_reports_show_only_the_users_area(): void
    {
        $this->send($this->woredaUser(), 'woredaOffice', 'school');
        $this->send($this->national, 'bureau', 'amharaBureau');

        Livewire::actingAs($this->woredaUser())
            ->test(ManualTrackingReports::class)
            ->assertSee('Ifa Bula Primary School')
            ->assertDontSee('Amhara Education Bureau')
            ->assertViewHas('summary', fn ($summary) => $summary['total_distributions'] === 1 && $summary['total_books'] === 2);

        Livewire::actingAs($this->national)
            ->test(ManualTrackingReports::class)
            ->assertSee('Amhara Education Bureau')
            ->assertViewHas('summary', fn ($summary) => $summary['total_distributions'] === 2);
    }

    private function send(User $actor, string $from, string $to, string $book = 'oromia')
    {
        return $this->shipments->dispatch($actor, [
            'from_organization_id' => $this->places[$from]->id,
            'to_organization_id' => $this->places[$to]->id,
            'lines' => [['manual_book_id' => $this->books[$book]->id, 'full_packages' => 1, 'partial_package_books' => 0, 'partial_teacher_guides' => 0]],
        ]);
    }

    private function assertRefused(User $actor, string $from, string $to): void
    {
        try {
            $this->send($actor, $from, $to);
            $this->fail("Sending from {$from} to {$to} should have been refused.");
        } catch (ManualTrackingException $exception) {
            $this->assertSame('You can only send to organizations in your area.', $exception->getMessage());
        }
    }

    private function ids($items): array
    {
        return collect($items)->pluck('id')->map(fn ($id) => (int) $id)->sort()->values()->all();
    }

    private function regionUser(): User
    {
        return User::factory()->create(['access_level' => User::ACCESS_LEVEL_REGION, 'region_id' => $this->places['oromia']->id]);
    }

    private function zoneUser(): User
    {
        return User::factory()->create([
            'access_level' => User::ACCESS_LEVEL_ZONE,
            'region_id' => $this->places['oromia']->id,
            'zone_id' => $this->places['eastShewa']->id,
        ]);
    }

    private function woredaUser(): User
    {
        return User::factory()->create([
            'access_level' => User::ACCESS_LEVEL_WOREDA,
            'region_id' => $this->places['oromia']->id,
            'zone_id' => $this->places['eastShewa']->id,
            'woreda_id' => $this->places['adama']->id,
        ]);
    }
}
