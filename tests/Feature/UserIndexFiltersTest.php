<?php

namespace Tests\Feature;

use App\Models\Country;
use App\Models\Organization;
use App\Models\Region;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserIndexFiltersTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Region $oromia;
    private Region $amhara;

    protected function setUp(): void
    {
        parent::setUp();

        Permission::findOrCreate('users.view', 'web');
        $officer = Role::create(['name' => 'Region Officer', 'guard_name' => 'web']);
        $schoolUser = Role::create(['name' => 'School User', 'guard_name' => 'web']);

        $country = Country::query()->create(['name' => 'Ethiopia', 'code' => 'ET']);
        $this->oromia = Region::query()->create(['name' => 'Oromia', 'code' => 'OR', 'country_id' => $country->id]);
        $this->amhara = Region::query()->create(['name' => 'Amhara', 'code' => 'AM', 'country_id' => $country->id]);
        $school = Organization::query()->create(['name' => 'Ifa Bula Primary School', 'country_id' => $country->id, 'region_id' => $this->oromia->id]);

        $this->admin = User::factory()->create(['name' => 'National Admin', 'email' => 'admin@example.test', 'access_level' => User::ACCESS_LEVEL_NATIONAL]);
        $this->admin->givePermissionTo('users.view');

        User::factory()->create([
            'name' => 'Abebe Kebede', 'email' => 'oromia.officer@example.test', 'access_level' => User::ACCESS_LEVEL_REGION,
            'country_id' => $country->id, 'region_id' => $this->oromia->id,
        ])->assignRole($officer);

        User::factory()->create([
            'name' => 'Hana Tesfaye', 'email' => 'amhara.officer@example.test', 'access_level' => User::ACCESS_LEVEL_REGION,
            'country_id' => $country->id, 'region_id' => $this->amhara->id,
        ])->assignRole($officer);

        User::factory()->create([
            'name' => 'Lensa Desta', 'email' => 'director@example.test', 'access_level' => User::ACCESS_LEVEL_ORGANIZATION,
            'country_id' => $country->id, 'region_id' => $this->oromia->id, 'organization_id' => $school->id,
        ])->assignRole($schoolUser);
    }

    public function test_search_matches_name_email_and_organization(): void
    {
        $this->actingAs($this->admin)->get(route('users.index', ['q' => 'abebe']))
            ->assertOk()
            ->assertSee('oromia.officer@example.test')
            ->assertDontSee('amhara.officer@example.test')
            ->assertDontSee('director@example.test');

        $this->actingAs($this->admin)->get(route('users.index', ['q' => 'amhara.officer']))
            ->assertSee('amhara.officer@example.test')
            ->assertDontSee('oromia.officer@example.test');

        $this->actingAs($this->admin)->get(route('users.index', ['q' => 'Ifa Bula']))
            ->assertSee('director@example.test')
            ->assertDontSee('oromia.officer@example.test');
    }

    public function test_role_access_level_and_region_filters_combine(): void
    {
        $this->actingAs($this->admin)->get(route('users.index', ['role' => 'Region Officer']))
            ->assertSee('oromia.officer@example.test')
            ->assertSee('amhara.officer@example.test')
            ->assertDontSee('director@example.test');

        $this->actingAs($this->admin)->get(route('users.index', ['access_level' => 'organization']))
            ->assertSee('director@example.test')
            ->assertDontSee('oromia.officer@example.test');

        $this->actingAs($this->admin)->get(route('users.index', ['role' => 'Region Officer', 'region_id' => $this->amhara->id]))
            ->assertSee('amhara.officer@example.test')
            ->assertDontSee('oromia.officer@example.test')
            ->assertSee('match your filters');
    }

    public function test_unknown_filter_values_are_ignored(): void
    {
        $this->actingAs($this->admin)->get(route('users.index', ['role' => 'No Such Role', 'access_level' => 'galaxy', 'region_id' => 999]))
            ->assertOk()
            ->assertSee('oromia.officer@example.test')
            ->assertSee('director@example.test')
            ->assertDontSee('match your filters');
    }

    public function test_region_filter_cannot_widen_a_regional_users_scope(): void
    {
        $regional = User::query()->where('email', 'oromia.officer@example.test')->firstOrFail();
        $regional->givePermissionTo('users.view');

        $this->actingAs($regional)->get(route('users.index', ['region_id' => $this->amhara->id]))
            ->assertOk()
            ->assertSee('director@example.test')
            ->assertDontSee('amhara.officer@example.test')
            ->assertDontSee('id="filter-region"', false);
    }
}
