<?php

namespace Tests\Feature;

use App\Models\Country;
use App\Models\Region;
use App\Models\User;
use App\Models\Woreda;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Managers can only give roles whose permissions they already hold. Roles a user has that
 * the manager cannot give are shown but left untouched.
 */
class UserRoleAssignmentTest extends TestCase
{
    use RefreshDatabase;

    private User $manager;
    private array $location;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['users.view', 'users.create', 'users.edit', 'roles.edit', 'school-manage', 'stock-manage', 'title-manage'] as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        Role::create(['name' => 'Woreda Officer', 'guard_name' => 'web'])
            ->givePermissionTo(['users.view', 'users.create', 'users.edit', 'roles.edit', 'school-manage', 'stock-manage']);
        Role::create(['name' => 'School User', 'guard_name' => 'web'])->givePermissionTo('school-manage');
        // Organization scope, but holds a permission the woreda officer does not have
        Role::create(['name' => 'Data Clerk', 'guard_name' => 'web'])->givePermissionTo(['school-manage', 'title-manage']);
        Role::create(['name' => 'Super-Admin', 'guard_name' => 'web']);

        $country = Country::query()->create(['name' => 'Ethiopia', 'code' => 'ET']);
        $region = Region::query()->create(['name' => 'Oromia', 'code' => 'OR', 'country_id' => $country->id]);
        $zone = Zone::query()->create(['name' => 'East Shewa Zone', 'code' => 'ESZ', 'region_id' => $region->id, 'country_id' => $country->id]);
        $woreda = Woreda::query()->create(['name' => 'Adama Woreda', 'code' => 'ADW', 'zone_id' => $zone->id]);

        $this->location = [
            'country_id' => $country->id, 'region_id' => $region->id,
            'zone_id' => $zone->id, 'woreda_id' => $woreda->id,
        ];

        $this->manager = User::factory()->create(['access_level' => User::ACCESS_LEVEL_WOREDA] + $this->location);
        $this->manager->assignRole('Woreda Officer');
    }

    public function test_only_roles_within_the_managers_permissions_are_listed(): void
    {
        $this->assertSame(['School User', 'Woreda Officer'], $this->manager->assignableRoles()->pluck('name')->all());

        $this->actingAs($this->manager)->get(route('users.create'))
            ->assertOk()
            ->assertSee('value="School User"', false)
            ->assertDontSee('value="Data Clerk"', false)
            ->assertDontSee('value="Super-Admin"', false);

        $superAdmin = User::factory()->create(['access_level' => User::ACCESS_LEVEL_NATIONAL]);
        $superAdmin->assignRole('Super-Admin');
        $this->assertSame(['Data Clerk', 'School User', 'Super-Admin', 'Woreda Officer'], $superAdmin->assignableRoles()->pluck('name')->all());
    }

    public function test_a_role_outside_the_managers_permissions_is_refused(): void
    {
        $this->actingAs($this->manager)
            ->post(route('users.store'), $this->form('clerk@example.test', ['Data Clerk']))
            ->assertSessionHasErrors(['roles' => 'You can only give roles whose permissions you already have.']);
        $this->assertDatabaseMissing('users', ['email' => 'clerk@example.test']);

        $this->actingAs($this->manager)
            ->post(route('users.store'), $this->form('officer@example.test', ['Woreda Officer']))
            ->assertSessionHasNoErrors();
        $this->assertTrue(User::query()->where('email', 'officer@example.test')->firstOrFail()->hasRole('Woreda Officer'));
    }

    public function test_roles_the_manager_cannot_give_are_kept_when_editing(): void
    {
        $user = User::factory()->create(['email' => 'clerk@example.test', 'access_level' => User::ACCESS_LEVEL_WOREDA] + $this->location);
        $user->assignRole(['Data Clerk', 'School User']);

        $this->actingAs($this->manager)->get(route('users.edit', $user->id))
            ->assertOk()
            ->assertSee('Greyed-out roles have permissions you do not hold.');

        // Unticking everything the manager can give leaves the role they cannot touch
        $this->actingAs($this->manager)
            ->put(route('users.update', $user->id), $this->form($user->email, []))
            ->assertSessionHasNoErrors();
        $this->assertSame(['Data Clerk'], $user->fresh()->roles->pluck('name')->all());

        $this->actingAs($this->manager)
            ->put(route('users.update', $user->id), $this->form($user->email, ['Data Clerk', 'Woreda Officer']))
            ->assertSessionHasNoErrors();
        $this->assertSame(['Data Clerk', 'Woreda Officer'], $user->fresh()->roles->pluck('name')->sort()->values()->all());
    }

    private function form(string $email, array $roles): array
    {
        return [
            'name' => 'New Person', 'email' => $email,
            'password' => 'secret123', 'confirm-password' => 'secret123',
            'roles' => $roles, 'access_level' => User::ACCESS_LEVEL_WOREDA,
        ] + $this->location;
    }
}
