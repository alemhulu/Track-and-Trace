<?php

namespace Tests\Feature;

use App\Models\Country;
use App\Models\Organization;
use App\Models\Region;
use App\Models\User;
use App\Models\Woreda;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlignPlacesTest extends TestCase
{
    use RefreshDatabase;

    private Region $snnp;
    private Region $spare;
    private Zone $hadiya;
    private Zone $gamo;
    private string $map;

    protected function setUp(): void
    {
        parent::setUp();

        $country = Country::query()->create(['name' => 'Ethiopia', 'code' => 'ET']);
        $this->snnp = Region::query()->create(['name' => 'SOMALI EDUCATION BUREAU, ETHIOPIA', 'code' => '07', 'country_id' => $country->id]);
        $this->spare = Region::query()->create(['name' => 'SW/ETHIOPIA EDUCATION BUREAU, ETHIOPIA', 'code' => '14', 'country_id' => $country->id]);
        $this->hadiya = Zone::query()->create(['name' => 'Hadiya', 'code' => '11', 'region_id' => $this->snnp->id, 'country_id' => $country->id]);
        $this->gamo = Zone::query()->create(['name' => 'Gamo', 'code' => '07', 'region_id' => $this->snnp->id, 'country_id' => $country->id]);

        $woreda = Woreda::query()->create(['name' => 'Lemo', 'code' => 'L1', 'zone_id' => $this->hadiya->id]);
        Organization::query()->create(['name' => 'Hosaena Primary', 'country_id' => $country->id, 'region_id' => $this->snnp->id, 'zone_id' => $this->hadiya->id, 'woreda_id' => $woreda->id]);
        Organization::query()->create(['name' => 'Arba Minch Primary', 'country_id' => $country->id, 'region_id' => $this->snnp->id, 'zone_id' => $this->gamo->id]);
        User::factory()->create(['region_id' => $this->snnp->id, 'zone_id' => $this->hadiya->id]);

        $this->map = tempnam(sys_get_temp_dir(), 'map');
        file_put_contents($this->map, json_encode([
            'regions' => [
                ['id' => $this->snnp->id, 'from' => 'SOMALI EDUCATION BUREAU, ETHIOPIA', 'to' => 'South Ethiopia'],
                ['id' => $this->spare->id, 'from' => 'SW/ETHIOPIA EDUCATION BUREAU, ETHIOPIA', 'to' => 'Central Ethiopia'],
            ],
            'move_zones' => [['to_region' => $this->spare->id, 'zones' => [$this->hadiya->id]]],
            'zone_codes' => [
                ['zone' => $this->hadiya->id, 'from_name' => 'Hadiya', 'code' => '0302'],
                ['zone' => $this->gamo->id, 'from_name' => 'Gamo', 'code' => '0204'],
            ],
        ]));
    }

    protected function tearDown(): void
    {
        @unlink($this->map);
        parent::tearDown();
    }

    public function test_a_dry_run_shows_the_changes_and_saves_nothing(): void
    {
        $this->artisan('manual:align-places', ['map' => $this->map])
            ->expectsOutputToContain('"SOMALI EDUCATION BUREAU, ETHIOPIA" → "South Ethiopia"')
            ->expectsOutputToContain('1 zones moved to Central Ethiopia')
            ->expectsOutputToContain('Run again with --commit')
            ->assertSuccessful();

        $this->assertSame('SOMALI EDUCATION BUREAU, ETHIOPIA', $this->snnp->fresh()->name);
        $this->assertSame($this->snnp->id, $this->hadiya->fresh()->region_id);
        $this->assertSame('11', $this->hadiya->fresh()->code);
    }

    public function test_commit_renames_moves_everything_in_the_zone_and_sets_codes(): void
    {
        $this->artisan('manual:align-places', ['map' => $this->map, '--commit' => true])->assertSuccessful();

        $this->assertSame('South Ethiopia', $this->snnp->fresh()->name);
        $this->assertSame('Central Ethiopia', $this->spare->fresh()->name);

        $this->assertSame($this->spare->id, $this->hadiya->fresh()->region_id);
        $this->assertSame($this->spare->id, Organization::query()->where('name', 'Hosaena Primary')->value('region_id'));
        $this->assertSame($this->spare->id, Woreda::query()->where('name', 'Lemo')->value('region_id'));
        $this->assertSame($this->spare->id, User::query()->where('zone_id', $this->hadiya->id)->value('region_id'));

        // The other zone and its school stay where they were
        $this->assertSame($this->snnp->id, $this->gamo->fresh()->region_id);
        $this->assertSame($this->snnp->id, Organization::query()->where('name', 'Arba Minch Primary')->value('region_id'));

        $this->assertSame('0302', $this->hadiya->fresh()->code);
        $this->assertSame('0204', $this->gamo->fresh()->code);

        // Running it again changes nothing
        $this->artisan('manual:align-places', ['map' => $this->map, '--commit' => true])
            ->expectsOutputToContain('Already aligned')
            ->assertSuccessful();
    }

    public function test_a_map_that_does_not_fit_the_data_saves_nothing(): void
    {
        $this->snnp->update(['name' => 'Someone renamed it']);

        $this->artisan('manual:align-places', ['map' => $this->map, '--commit' => true])
            ->expectsOutputToContain('was not renamed')
            ->expectsOutputToContain('Nothing was saved')
            ->assertFailed();

        $this->assertSame('SW/ETHIOPIA EDUCATION BUREAU, ETHIOPIA', $this->spare->fresh()->name);
        $this->assertSame($this->snnp->id, $this->hadiya->fresh()->region_id);
        $this->assertSame('11', $this->hadiya->fresh()->code);
    }
}
