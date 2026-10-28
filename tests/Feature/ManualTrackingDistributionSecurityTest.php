<?php

namespace Tests\Feature;

use App\Http\Livewire\ManualTracking\Book\AddManualBook;
use App\Http\Livewire\ManualTracking\Distribution\ListManualDistribution;
use App\Http\Livewire\ManualTracking\Shipment\CreateShipment;
use App\Http\Livewire\ManualTracking\Shipment\ShowShipment;
use App\Models\Country;
use App\Models\Grade;
use App\Models\ManualTracking\ManualBook;
use App\Models\ManualTracking\ManualBookCode;
use App\Models\ManualTracking\ManualDistribution;
use App\Models\ManualTracking\ManualStockBalance;
use App\Models\Organization;
use App\Models\Region;
use App\Models\Subject;
use App\Models\User;
use App\Models\Woreda;
use App\Models\Zone;
use App\Services\ManualTracking\ManualShipmentService;
use App\Services\ManualTracking\ManualStockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * The manual tracking screens enforce the same area rules as the services underneath.
 */
class ManualTrackingDistributionSecurityTest extends TestCase
{
    use RefreshDatabase;

    private array $east;
    private array $west;
    private ManualBook $book;

    protected function setUp(): void
    {
        parent::setUp();

        $country = Country::query()->create(['name' => 'Ethiopia', 'code' => 'ET']);
        $region = Region::query()->create(['name' => 'Oromia', 'code' => 'OR', 'country_id' => $country->id]);
        $this->east = $this->makeZone($country, $region, 'East Shewa');
        $this->west = $this->makeZone($country, $region, 'West Arsi');

        $this->book = ManualBook::query()->create(['title' => 'Oromia Grade 6 Mathematics', 'region_id' => $region->id]);
        $national = User::factory()->create(['access_level' => User::ACCESS_LEVEL_NATIONAL]);
        app(ManualStockService::class)->recordOpeningStock($national, $this->east['office'], $this->book, ['student' => 800, 'teacher' => 20]);
        app(ManualStockService::class)->recordOpeningStock($national, $this->west['office'], $this->book, ['student' => 800, 'teacher' => 20]);
    }

    public function test_zone_officer_dispatches_a_shipment_from_their_office(): void
    {
        Livewire::actingAs($this->east['officer'])
            ->test(CreateShipment::class)
            ->set('from_organization_id', $this->east['office']->id)
            ->set('to_organization_id', $this->east['school']->id)
            ->set('lines.0.manual_book_id', $this->book->id)
            ->set('lines.0.full_packages', 3)
            ->set('lines.0.partial_package_books', 12)
            ->set('lines.0.partial_teacher_guides', 1)
            ->call('dispatchShipment')
            ->assertHasNoErrors()
            ->assertRedirect();

        $shipment = ManualDistribution::query()->with('lines')->sole();
        $this->assertSame(ManualDistribution::STATUS_DISPATCHED, $shipment->status);
        $this->assertSame([132, 4], [$shipment->lines[0]->quantity, $shipment->lines[0]->teacher_guides]);
        $this->assertSame(668, $this->onHand($this->east['office'], 'student'));
    }

    public function test_tampered_source_outside_the_officers_area_is_rejected(): void
    {
        Livewire::actingAs($this->east['officer'])
            ->test(CreateShipment::class)
            ->set('from_organization_id', $this->west['office']->id)
            ->assertSet('from_organization_id', '')
            ->set('to_organization_id', $this->east['school']->id)
            ->set('lines.0.manual_book_id', $this->book->id)
            ->set('lines.0.full_packages', 1)
            ->call('dispatchShipment')
            ->assertHasErrors(['from_organization_id' => 'required']);

        $this->assertSame(0, ManualDistribution::query()->count());
        $this->assertSame(800, $this->onHand($this->west['office'], 'student'));
    }

    public function test_shipment_list_and_detail_are_limited_to_the_officers_area(): void
    {
        $shipment = app(ManualShipmentService::class)->dispatch($this->west['officer'], [
            'from_organization_id' => $this->west['office']->id,
            'to_organization_id' => $this->west['school']->id,
            'lines' => [['manual_book_id' => $this->book->id, 'full_packages' => 1]],
        ]);

        Livewire::actingAs($this->west['officer'])
            ->test(ListManualDistribution::class)
            ->assertSee($shipment->reference)
            ->assertSee($this->west['school']->name);

        Livewire::actingAs($this->east['officer'])
            ->test(ListManualDistribution::class)
            ->assertDontSee($shipment->reference);

        $this->actingAs($this->east['officer'])
            ->get(route('manual-tracking.shipments.show', $shipment->id))
            ->assertNotFound();

        $this->actingAs($this->west['officer'])
            ->get(route('manual-tracking.shipments.show', $shipment->id))
            ->assertOk()
            ->assertSee($shipment->reference);
    }

    public function test_only_the_destination_side_sees_and_uses_the_receive_form(): void
    {
        $shipment = app(ManualShipmentService::class)->dispatch($this->east['officer'], [
            'from_organization_id' => $this->east['office']->id,
            'to_organization_id' => $this->east['school']->id,
            'lines' => [['manual_book_id' => $this->book->id, 'full_packages' => 2]],
        ]);
        $lineId = $shipment->lines->first()->id;

        Livewire::actingAs($this->east['officer'])
            ->test(ShowShipment::class, ['shipment' => $shipment->id])
            ->assertDontSee('Confirm Receipt');

        Livewire::actingAs($this->east['director'])
            ->test(ShowShipment::class, ['shipment' => $shipment->id])
            ->assertSee('Confirm Receipt')
            ->set("counts.{$lineId}.full_packages", 1)
            ->call('receive')
            ->assertSee('Received with Discrepancy');

        $this->assertSame(40, $this->onHand($this->east['school'], 'student'));
        $this->assertSame(ManualDistribution::STATUS_RECEIVED_WITH_DISCREPANCY, $shipment->fresh()->status);
    }

    public function test_title_form_links_printed_qr_codes(): void
    {
        $region = Region::query()->firstOrFail();
        $grade = Grade::query()->create(['name' => '8']);
        $subject = Subject::query()->create(['name' => 'Social Studies']);
        Role::query()->create(['name' => 'Admin', 'guard_name' => 'web']);
        $admin = User::factory()->create(['access_level' => User::ACCESS_LEVEL_NATIONAL]);
        $admin->assignRole('Admin');

        Livewire::actingAs($admin)
            ->test(AddManualBook::class)
            ->set('region_id', $region->id)
            ->set('grade_id', $grade->id)
            ->set('subject_id', $subject->id)
            ->assertSet('title', 'Oromia Grade 8 Social Studies')
            ->assertSet('codes.teacher.payload', 'Oromia Grade 8 Social Studies Teacher guide')
            ->set('codes.teacher.print_code', '25110-031-1-1720')
            ->call('saveBook')
            ->assertHasNoErrors();

        $code = ManualBookCode::findByScan('oromia grade 8 social studies TEACHER GUIDE');
        $this->assertSame('teacher', $code?->edition);
        $this->assertSame('Oromia Grade 8 Social Studies', $code->book->title);
        $this->assertSame('25110-031-1-1720', $code->print_code);
    }

    private function makeZone(Country $country, Region $region, string $name): array
    {
        $zone = Zone::query()->create(['name' => "{$name} Zone", 'region_id' => $region->id, 'country_id' => $country->id]);
        $woreda = Woreda::query()->create(['name' => "{$name} Woreda", 'zone_id' => $zone->id]);
        $office = Organization::query()->create(['name' => "{$name} Zone Education Department", 'country_id' => $country->id, 'region_id' => $region->id, 'zone_id' => $zone->id]);
        $school = Organization::query()->create(['name' => "{$name} Primary School", 'country_id' => $country->id, 'region_id' => $region->id, 'zone_id' => $zone->id, 'woreda_id' => $woreda->id]);

        return [
            'zone' => $zone,
            'office' => $office,
            'school' => $school,
            'officer' => User::factory()->create(['access_level' => User::ACCESS_LEVEL_ZONE, 'region_id' => $region->id, 'zone_id' => $zone->id]),
            'director' => User::factory()->create([
                'access_level' => User::ACCESS_LEVEL_ORGANIZATION,
                'organization_id' => $school->id,
                'region_id' => $region->id,
                'zone_id' => $zone->id,
                'woreda_id' => $woreda->id,
            ]),
        ];
    }

    private function onHand(Organization $organization, string $edition): int
    {
        return (int) ManualStockBalance::query()
            ->where('organization_id', $organization->id)
            ->where('manual_book_id', $this->book->id)
            ->where('edition', $edition)
            ->value('on_hand');
    }
}
