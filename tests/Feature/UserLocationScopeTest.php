<?php

namespace Tests\Feature;

use App\Http\Livewire\Users\LocationCascade;
use App\Models\Country;
use App\Models\Organization;
use App\Models\Region;
use App\Models\User;
use App\Models\Woreda;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Creating and editing users: the manager's own region, zone, woreda and organization are
 * filled in and locked, and the server refuses anything outside them.
 */
class UserLocationScopeTest extends TestCase
{
    use RefreshDatabase;

    private array $places;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['users.create', 'users.edit'] as $permission) {
            Permission::findOrCreate($permission, 'web');
        }
        foreach (['Woreda Officer', 'Organization User'] as $role) {
            Role::create(['name' => $role, 'guard_name' => 'web']);
        }

        $country = Country::query()->create(['name' => 'Ethiopia', 'code' => 'ET']);
        $oromia = Region::query()->create(['name' => 'Oromia', 'code' => 'OR', 'country_id' => $country->id]);
        $amhara = Region::query()->create(['name' => 'Amhara', 'code' => 'AM', 'country_id' => $country->id]);
        $eastShewa = Zone::query()->create(['name' => 'East Shewa Zone', 'code' => 'ESZ', 'region_id' => $oromia->id, 'country_id' => $country->id]);
        $jimma = Zone::query()->create(['name' => 'Jimma Zone', 'code' => 'JMZ', 'region_id' => $oromia->id, 'country_id' => $country->id]);
        $adama = Woreda::query()->create(['name' => 'Adama Woreda', 'code' => 'ADW', 'zone_id' => $eastShewa->id]);
        $bishoftu = Woreda::query()->create(['name' => 'Bishoftu Woreda', 'code' => 'BSW', 'zone_id' => $eastShewa->id]);
        $school = Organization::query()->create([
            'name' => 'Ifa Bula Primary School', 'country_id' => $country->id,
            'region_id' => $oromia->id, 'zone_id' => $eastShewa->id, 'woreda_id' => $adama->id,
        ]);

        $this->places = compact('country', 'oromia', 'amhara', 'eastShewa', 'jimma', 'adama', 'bishoftu', 'school');
    }

    public function test_each_manager_level_starts_with_its_own_location_locked(): void
    {
        $id = fn (string $place) => $this->places[$place]->id;

        Livewire::actingAs($this->manager(User::ACCESS_LEVEL_REGION))
            ->test(LocationCascade::class, ['initialAccessLevel' => User::ACCESS_LEVEL_WOREDA])
            ->assertSet('regionId', $id('oromia'))
            ->assertSet('zoneId', null)
            ->assertSee($this->lockedInput('region_id', $id('oromia')), false)
            ->assertDontSee('<input type="hidden" name="zone_id"', false);

        Livewire::actingAs($this->manager(User::ACCESS_LEVEL_ZONE))
            ->test(LocationCascade::class, ['initialAccessLevel' => User::ACCESS_LEVEL_WOREDA])
            ->assertSet('regionId', $id('oromia'))
            ->assertSet('zoneId', $id('eastShewa'))
            ->assertSet('woredaId', null)
            ->assertSee($this->lockedInput('zone_id', $id('eastShewa')), false)
            ->assertDontSee('<input type="hidden" name="woreda_id"', false);

        Livewire::actingAs($this->manager(User::ACCESS_LEVEL_WOREDA))
            ->test(LocationCascade::class, ['initialAccessLevel' => User::ACCESS_LEVEL_ORGANIZATION])
            ->assertSet('regionId', $id('oromia'))
            ->assertSet('zoneId', $id('eastShewa'))
            ->assertSet('woredaId', $id('adama'))
            ->assertSet('organizations', fn ($organizations) => collect($organizations)->pluck('id')->all() === [$id('school')])
            ->assertSee($this->lockedInput('woreda_id', $id('adama')), false)
            ->assertDontSee('<input type="hidden" name="organization_id"', false);

        Livewire::actingAs($this->manager(User::ACCESS_LEVEL_ORGANIZATION))
            ->test(LocationCascade::class, ['initialAccessLevel' => User::ACCESS_LEVEL_ORGANIZATION])
            ->assertSet('accessLevelOptions', [User::ACCESS_LEVEL_ORGANIZATION])
            ->assertSet('organizationId', $id('school'))
            ->assertSet('woredaId', $id('adama'))
            ->assertSee($this->lockedInput('organization_id', $id('school')), false);
    }

    public function test_only_the_fields_the_access_level_needs_are_shown(): void
    {
        $shown = [
            User::ACCESS_LEVEL_NATIONAL => [],
            User::ACCESS_LEVEL_REGION => ['region'],
            User::ACCESS_LEVEL_ZONE => ['region', 'zone'],
            User::ACCESS_LEVEL_WOREDA => ['region', 'zone', 'woreda'],
            User::ACCESS_LEVEL_ORGANIZATION => ['region', 'zone', 'woreda', 'organization'],
        ];

        $national = User::factory()->create(['access_level' => User::ACCESS_LEVEL_NATIONAL]);
        $cascade = Livewire::actingAs($national)->test(LocationCascade::class);

        foreach ($shown as $level => $fields) {
            $cascade->set('accessLevel', $level);

            foreach (['region', 'zone', 'woreda', 'organization'] as $field) {
                in_array($field, $fields, true)
                    ? $cascade->assertSee('id="' . $field . '-row"', false)
                    : $cascade->assertDontSee('id="' . $field . '-row"', false);
            }
        }
    }

    public function test_locked_levels_cannot_be_changed_in_the_form(): void
    {
        Livewire::actingAs($this->manager(User::ACCESS_LEVEL_ZONE))
            ->test(LocationCascade::class, ['initialAccessLevel' => User::ACCESS_LEVEL_WOREDA])
            ->set('regionId', $this->places['amhara']->id)
            ->assertSet('regionId', $this->places['oromia']->id)
            ->set('zoneId', $this->places['jimma']->id)
            ->assertSet('zoneId', $this->places['eastShewa']->id)
            ->set('woredaId', $this->places['bishoftu']->id)
            ->assertSet('woredaId', $this->places['bishoftu']->id);
    }

    public function test_the_server_refuses_users_outside_the_managers_area(): void
    {
        $manager = $this->manager(User::ACCESS_LEVEL_WOREDA);
        $user = fn (string $woreda) => [
            'name' => 'New Officer', 'email' => "officer.{$woreda}@example.test",
            'password' => 'secret123', 'confirm-password' => 'secret123', 'roles' => ['Woreda Officer'],
            'access_level' => User::ACCESS_LEVEL_WOREDA, 'country_id' => $this->places['country']->id,
            'region_id' => $this->places['oromia']->id, 'zone_id' => $this->places['eastShewa']->id, 'woreda_id' => $this->places[$woreda]->id,
        ];

        $this->actingAs($manager)->post(route('users.store'), $user('bishoftu'))->assertSessionHasErrors('woreda_id');
        $this->assertDatabaseMissing('users', ['email' => 'officer.bishoftu@example.test']);

        $this->actingAs($manager)->post(route('users.store'), $user('adama'))->assertSessionHasNoErrors();
        $created = User::query()->where('email', 'officer.adama@example.test')->firstOrFail();
        $this->assertSame($this->places['adama']->id, (int) $created->woreda_id);

        $this->actingAs($manager)
            ->put(route('users.update', $created->id), ['woreda_id' => $this->places['bishoftu']->id, 'email' => $created->email] + $user('bishoftu'))
            ->assertSessionHasErrors('woreda_id');
        $this->assertSame($this->places['adama']->id, (int) $created->fresh()->woreda_id);
    }

    // A locked select is disabled, so its value is sent by a hidden input instead
    private function lockedInput(string $name, int $value): string
    {
        return '<input type="hidden" name="' . $name . '" value="' . $value . '">';
    }

    private function manager(string $level): User
    {
        $location = match ($level) {
            User::ACCESS_LEVEL_REGION => ['region_id' => $this->places['oromia']->id],
            User::ACCESS_LEVEL_ZONE => ['region_id' => $this->places['oromia']->id, 'zone_id' => $this->places['eastShewa']->id],
            User::ACCESS_LEVEL_WOREDA => ['region_id' => $this->places['oromia']->id, 'zone_id' => $this->places['eastShewa']->id, 'woreda_id' => $this->places['adama']->id],
            User::ACCESS_LEVEL_ORGANIZATION => ['organization_id' => $this->places['school']->id],
        };

        $manager = User::factory()->create(['access_level' => $level, 'country_id' => $this->places['country']->id] + $location);
        $manager->givePermissionTo(['users.create', 'users.edit']);

        return $manager;
    }
}
