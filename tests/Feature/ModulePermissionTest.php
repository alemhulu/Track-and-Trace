<?php

namespace Tests\Feature;

use App\Http\Livewire\Book\Subject\AddSubject;
use App\Http\Livewire\Location\Modal\EditRegion;
use App\Http\Livewire\Location\Region as RegionScreen;
use App\Models\Country;
use App\Models\Region;
use App\Models\Subject;
use App\Models\User;
use App\Support\PermissionCatalog;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * Permissions are catalogued per sidebar module (config/permissions.php) and decide which
 * modules a user sees and opens.
 */
class ModulePermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_exactly_the_catalog(): void
    {
        $this->seed(PermissionSeeder::class);

        $this->assertEqualsCanonicalizing(PermissionCatalog::names(), Permission::query()->pluck('name')->all());
        $this->assertTrue(Role::findByName('Admin')->hasAllPermissions(PermissionCatalog::names()));
        $this->assertFalse(Role::findByName('School User')->hasPermissionTo('manual-tracking.shipments.dispatch'));
    }

    public function test_a_module_opens_only_with_its_permission(): void
    {
        $this->seed(PermissionSeeder::class);
        $user = User::factory()->create(['access_level' => User::ACCESS_LEVEL_NATIONAL]);
        $user->givePermissionTo('dashboard.view');

        $this->actingAs($user)->get('/book')->assertForbidden();
        $this->actingAs($user)->get(route('book.list'))->assertForbidden();

        $user->givePermissionTo('books.view');

        $this->actingAs($user)->get('/book')->assertOk();
        $this->actingAs($user)->get(route('book.add'))->assertForbidden();
        $this->actingAs($user)->get(route('print-order'))->assertForbidden();
    }

    public function test_sidebar_shows_only_permitted_modules_and_their_sections(): void
    {
        $this->seed(PermissionSeeder::class);
        $user = User::factory()->create(['access_level' => User::ACCESS_LEVEL_NATIONAL]);
        $user->givePermissionTo(['books.view', 'manual-tracking.view']);

        $this->actingAs($user)->get('/book')
            ->assertOk()
            ->assertSee('href="/book"', false)
            ->assertSee('href="/manual-tracking"', false)
            ->assertDontSee('href="/print-order"', false)
            ->assertSee('>Supply Chain<', false)
            ->assertDontSee('href="/route"', false)
            ->assertDontSee('>Logistics<', false)
            ->assertDontSee('>Administration<', false);
    }

    public function test_dashboard_sends_users_without_it_to_their_first_module(): void
    {
        $this->seed(PermissionSeeder::class);
        $user = User::factory()->create(['access_level' => User::ACCESS_LEVEL_NATIONAL]);

        $user->givePermissionTo('trace.view');
        $this->actingAs($user)->get(route('dashboard'))->assertRedirect(route('trace'));

        $user->revokePermissionTo('trace.view');
        $this->actingAs($user->fresh())->get(route('dashboard'))->assertForbidden();
    }

    public function test_role_editor_groups_permissions_by_sidebar_section(): void
    {
        $this->seed(PermissionSeeder::class);
        $admin = User::factory()->create(['access_level' => User::ACCESS_LEVEL_NATIONAL]);
        $admin->assignRole('Admin');

        $this->actingAs($admin)->get(route('roles.create'))
            ->assertOk()
            ->assertSeeInOrder(['Overview', 'Supply Chain', 'Logistics', 'Field Operations', 'Administration'])
            ->assertSee('Dispatch shipments')
            ->assertSee('manual-tracking.shipments.dispatch');
    }

    public function test_migration_moves_old_grants_onto_module_permissions(): void
    {
        $migration = require database_path('migrations/2026_10_28_100000_rename_permissions_to_module_actions.php');

        // Back to the old names, as on a database that has not run the migration yet
        DB::table('permissions')->delete();
        $role = Role::create(['name' => 'Legacy Officer', 'guard_name' => 'web']);
        $role->givePermissionTo([
            Permission::create(['name' => 'view-books', 'guard_name' => 'web']),
            Permission::create(['name' => 'user-list', 'guard_name' => 'web']),
            Permission::create(['name' => 'manage-routes', 'guard_name' => 'web']),
        ]);
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::create(['name' => 'view-trace', 'guard_name' => 'web']));

        $migration->up();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // "view" of a module covers each of its screens, "manage" creating, editing and deleting
        $this->assertEqualsCanonicalizing(
            ['books.view', 'books.subjects.view', 'books.grades.view', 'books.types.view', 'books.print-types.view', 'books.paper-sizes.view',
                'users.view', 'routes.create', 'routes.edit', 'routes.delete'],
            $role->fresh()->permissions->pluck('name')->all()
        );
        $this->assertTrue($user->fresh()->hasDirectPermission('trace.view'));
        $this->assertEqualsCanonicalizing(PermissionCatalog::names(), Permission::query()->pluck('name')->all());
    }

    public function test_view_only_location_access_cannot_create_edit_or_delete(): void
    {
        $this->seed(PermissionSeeder::class);
        $country = Country::query()->create(['name' => 'Ethiopia', 'code' => 'ET']);
        $region = Region::query()->create(['name' => 'Oromia', 'code' => 'OR', 'country_id' => $country->id]);
        $officer = User::factory()->create(['access_level' => User::ACCESS_LEVEL_NATIONAL]);
        $officer->givePermissionTo(['locations.view', 'locations.regions.view']);

        $this->actingAs($officer)->get(route('location.region'))
            ->assertOk()
            ->assertSee('Oromia')
            ->assertDontSee('Add New Region')
            ->assertDontSee('icon-btn-edit')
            ->assertDontSee('icon-btn-delete');
        $this->actingAs($officer)->get(route('location.country'))->assertForbidden();

        // The actions are refused on the server too, not only hidden
        Livewire::actingAs($officer)->test(RegionScreen::class)
            ->set('name', 'Amhara')->set('is_city', 0)->set('country_id', $country->id)
            ->call('addRegion')
            ->assertForbidden();
        Livewire::actingAs($officer)->test(RegionScreen::class)->call('deleteRegion', $region->id)->assertForbidden();
        Livewire::actingAs($officer)->test(EditRegion::class)->call('editRegion', $region->id)->assertForbidden();
        $this->assertDatabaseHas('regions', ['id' => $region->id, 'name' => 'Oromia']);
        $this->assertDatabaseMissing('regions', ['name' => 'Amhara']);

        $officer->givePermissionTo('locations.regions.create');
        Livewire::actingAs($officer->fresh())->test(RegionScreen::class)
            ->set('name', 'Amhara')->set('is_city', 0)->set('country_id', $country->id)
            ->call('addRegion')
            ->assertHasNoErrors();
        $this->assertDatabaseHas('regions', ['name' => 'Amhara']);
    }

    public function test_one_form_for_create_and_edit_checks_the_right_permission(): void
    {
        $this->seed(PermissionSeeder::class);
        $subject = Subject::query()->create(['name' => 'Mathematics', 'code' => 'MAT']);
        $clerk = User::factory()->create(['access_level' => User::ACCESS_LEVEL_NATIONAL]);
        $clerk->givePermissionTo(['books.view', 'books.subjects.view', 'books.subjects.create']);

        Livewire::actingAs($clerk)->test(AddSubject::class)
            ->set('name', 'Physics')->set('code', 'PHY')
            ->call('addSubject')
            ->assertHasNoErrors();
        $this->assertDatabaseHas('subjects', ['name' => 'Physics']);

        // Creating does not let them change an existing subject, even by setting the id directly
        Livewire::actingAs($clerk)->test(AddSubject::class)->call('editSubject', $subject->id)->assertForbidden();
        Livewire::actingAs($clerk)->test(AddSubject::class)
            ->set('editingSubjectId', $subject->id)->set('name', 'Maths renamed')
            ->call('addSubject')
            ->assertForbidden();
        $this->assertDatabaseHas('subjects', ['id' => $subject->id, 'name' => 'Mathematics']);
    }

    public function test_role_editor_shows_sub_module_rows(): void
    {
        $this->seed(PermissionSeeder::class);
        $admin = User::factory()->create(['access_level' => User::ACCESS_LEVEL_NATIONAL]);
        $admin->assignRole('Admin');

        $this->actingAs($admin)->get(route('roles.create'))
            ->assertOk()
            ->assertSeeInOrder(['Locations', 'Countries', 'Regions / Cities', 'Zones / Sub-cities', 'Woredas', 'Kebeles'])
            ->assertSee('title="locations.regions.delete"', false);
    }
}
