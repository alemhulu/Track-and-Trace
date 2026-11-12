<?php

namespace Tests\Feature;

use App\Http\Livewire\ManualTracking\Oversight\VisitIndex;
use App\Http\Livewire\ManualTracking\Stock\ManualStockIndex;
use App\Models\Country;
use App\Models\Organization;
use App\Models\OrganizationType;
use App\Models\Region;
use App\Models\User;
use App\Models\Woreda;
use App\Models\Zone;
use App\Support\ScopedLocation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * The location picker lists organizations for the place chosen only, so a national user
 * with tens of thousands of schools in reach never loads them all on one page.
 */
class ManualTrackingPickerBoundTest extends TestCase
{
    use RefreshDatabase;

    private array $places;

    protected function setUp(): void
    {
        parent::setUp();
        $this->grantModuleAccessToNewUsers();

        $country = Country::query()->create(['name' => 'Ethiopia', 'code' => 'ET']);
        $region = Region::query()->create(['name' => 'Oromia', 'code' => 'OR', 'country_id' => $country->id]);
        $zone = Zone::query()->create(['name' => 'East Shewa Zone', 'code' => 'ESZ', 'region_id' => $region->id, 'country_id' => $country->id]);
        $woreda = Woreda::query()->create(['name' => 'Adama Woreda', 'code' => 'ADW', 'zone_id' => $zone->id]);
        $otherWoreda = Woreda::query()->create(['name' => 'Bishoftu Woreda', 'code' => 'BSW', 'zone_id' => $zone->id]);
        $school = OrganizationType::query()->create(['name' => 'School']);

        $org = fn (string $name, array $location, array $extra = []) => Organization::query()
            ->create(['name' => $name, 'country_id' => $country->id] + $location + $extra);
        $inWoreda = fn ($id) => ['region_id' => $region->id, 'zone_id' => $zone->id, 'woreda_id' => $id];

        $this->places = compact('country', 'region', 'zone', 'woreda', 'otherWoreda', 'school') + [
            'ministry' => $org('Ministry of Education', []),
            'bureau' => $org('Oromia Education Bureau', ['region_id' => $region->id]),
            'zoneOffice' => $org('East Shewa Zone Education Department', ['region_id' => $region->id, 'zone_id' => $zone->id]),
            'woredaOffice' => $org('Adama Woreda Education Office', $inWoreda($woreda->id)),
            'adamaSchool' => $org('Adama Primary School', $inWoreda($woreda->id), ['organization_type_id' => $school->id]),
            'bishoftuSchool' => $org('Bishoftu Primary School', $inWoreda($otherWoreda->id), ['organization_type_id' => $school->id]),
        ];
    }

    public function test_the_list_holds_only_the_place_chosen(): void
    {
        $location = ScopedLocation::for(User::factory()->create());
        $area = fn (array $chosen = []) => array_merge(['region_id' => '', 'zone_id' => '', 'woreda_id' => ''], $chosen);
        $listed = fn (array $picker) => $picker['organizations']->pluck('name')->sort()->values()->all();

        $nothing = $location->picker($area());
        $this->assertSame(['Ministry of Education'], $listed($nothing));
        $this->assertNotNull($nothing['organizationHint']);

        $this->assertSame(['Oromia Education Bureau'], $listed($location->picker($area(['region_id' => $this->places['region']->id]))));
        $this->assertSame(['East Shewa Zone Education Department'], $listed($location->picker($area([
            'region_id' => $this->places['region']->id, 'zone_id' => $this->places['zone']->id,
        ]))));

        $woreda = $location->picker($area([
            'region_id' => $this->places['region']->id, 'zone_id' => $this->places['zone']->id, 'woreda_id' => $this->places['woreda']->id,
        ]));
        $this->assertSame(['Adama Primary School', 'Adama Woreda Education Office'], $listed($woreda));
        $this->assertNull($woreda['organizationHint']);

        // The organization already chosen stays in the list wherever the cascade stands
        $this->assertSame(['Bishoftu Primary School', 'Ministry of Education'], $listed($location->picker($area(), null, $this->places['bishoftuSchool']->id)));

        // A caller's own query is bounded the same way and keeps its own conditions
        $nonSchools = Organization::query()->whereNull('organization_type_id');
        $this->assertSame(['Adama Woreda Education Office'], $listed($location->picker($area([
            'region_id' => $this->places['region']->id, 'zone_id' => $this->places['zone']->id, 'woreda_id' => $this->places['woreda']->id,
        ]), $nonSchools)));
    }

    public function test_a_locked_organization_stays_listed_and_validation_is_unchanged(): void
    {
        $school = User::factory()->create([
            'access_level' => User::ACCESS_LEVEL_ORGANIZATION,
            'organization_id' => $this->places['adamaSchool']->id,
        ]);
        $location = ScopedLocation::for($school);
        $area = $location->apply([]);
        $picker = $location->picker($area);

        $this->assertSame((string) $this->places['adamaSchool']->id, $picker['locked']['organization_id']);
        $this->assertTrue($picker['organizations']->contains('id', $this->places['adamaSchool']->id));
        $this->assertNull($picker['organizationHint']);

        // A national user may still pick any organization in reach, listed or not
        $national = ScopedLocation::for(User::factory()->create());
        $blank = $national->apply([]);
        $this->assertSame((string) $this->places['bishoftuSchool']->id, $national->organization($blank, $this->places['bishoftuSchool']->id));
    }

    public function test_pages_keep_the_chosen_filter_and_bound_the_visit_school_list(): void
    {
        $national = User::factory()->create();

        Livewire::actingAs($national)
            ->test(ManualStockIndex::class)
            ->set('organizationFilter', (string) $this->places['bishoftuSchool']->id)
            ->assertViewHas('locationPicker', fn ($picker) => $picker['organizations']->contains('id', $this->places['bishoftuSchool']->id)
                && ! $picker['organizations']->contains('id', $this->places['adamaSchool']->id));

        Livewire::actingAs($national)
            ->test(VisitIndex::class)
            ->assertViewHas('schools', fn ($schools) => $schools->isEmpty())
            ->set('area.region_id', (string) $this->places['region']->id)
            ->set('area.zone_id', (string) $this->places['zone']->id)
            ->set('area.woreda_id', (string) $this->places['woreda']->id)
            ->assertViewHas('schools', fn ($schools) => $schools->pluck('id')->all() === [$this->places['adamaSchool']->id]);
    }

    public function test_manual_tracking_pages_stay_small_with_tens_of_thousands_of_schools(): void
    {
        $now = now();
        foreach (array_chunk(range(1, 56000), 2000) as $chunk) {
            DB::table('organizations')->insert(array_map(fn ($n) => [
                'name' => "School {$n}",
                'country_id' => $this->places['country']->id,
                'region_id' => $this->places['region']->id,
                'zone_id' => $this->places['zone']->id,
                'woreda_id' => $n % 2 ? $this->places['woreda']->id : $this->places['otherWoreda']->id,
                'organization_type_id' => $this->places['school']->id,
                'created_at' => $now,
                'updated_at' => $now,
            ], $chunk));
        }

        $national = User::factory()->create();
        $this->actingAs($national)->get(route('manual-tracking.stock.index'))->assertOk(); // warm up the framework

        foreach ([
            'manual-tracking.stock.index',
            'manual-tracking.distribution.list',
            'manual-tracking.distribution.add',
            'manual-tracking.visits.index',
            'manual-tracking.scan.sort',
        ] as $page) {
            gc_collect_cycles();
            $before = memory_get_usage();
            memory_reset_peak_usage();

            $response = $this->actingAs($national)->get(route($page));

            $used = memory_get_peak_usage() - $before;
            $response->assertOk();
            $this->assertLessThan(40 * 1024 * 1024, $used, sprintf('%s used %.1f MB', $page, $used / 1048576));
            $this->assertLessThan(1024 * 1024, strlen($response->getContent()), "{$page} sent too large a page");
        }
    }
}
