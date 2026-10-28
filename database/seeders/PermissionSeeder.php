<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\PermissionCatalog;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissionNames = PermissionCatalog::names();

        foreach ($permissionNames as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);
        }

        // Every screen's view permission (sub-modules included), without the admin screens
        $views = array_values(array_filter($permissionNames, fn ($permission) => str_ends_with($permission, '.view')
            && ! in_array($permission, ['users.view', 'roles.view', 'logs.view'], true)));
        $locationViews = array_values(array_filter(PermissionCatalog::namesOf('locations'), fn ($permission) => str_ends_with($permission, '.view')));

        // Everything in manual tracking except registering titles, which stays with admins
        $titles = PermissionCatalog::namesOf('manual-tracking.titles');
        $manualTracking = array_values(array_diff(PermissionCatalog::namesOf('manual-tracking'), $titles));

        $roles = [
            'Super-Admin' => $permissionNames,
            'Admin' => $permissionNames,
            'National Admin' => array_values(array_diff($permissionNames, $titles)),
            'Region Officer' => [
                ...$views,
                ...$manualTracking,
                'users.view',
            ],
            'Zone Officer' => [
                ...$views,
                ...$manualTracking,
                'users.view',
            ],
            'Woreda Officer' => [
                ...$views,
                ...$manualTracking,
                'users.view',
                'packages.request',
                'packages.send',
                'distributions.create',
                'distributions.edit',
            ],
            'Organization User' => [
                'dashboard.view',
                'books.view',
                'packages.view',
                'distributions.view',
                'warehouses.view',
                'organizations.view',
                ...$locationViews,
                ...$manualTracking,
            ],
            'School User' => [
                'dashboard.view',
                'dashboard.inventory.view',
                'dashboard.distribution.view',
                'books.view',
                'manual-tracking.view',
                'manual-tracking.shipments.receive',
                'manual-tracking.schools.view',
                'manual-tracking.schools.enrol',
                'manual-tracking.schools.issue',
                'manual-tracking.reports.view',
            ],
            'Org-Manager' => [
                'dashboard.view',
                'users.view',
                'books.view',
                'books.create',
                'books.edit',
                'print-orders.view',
                'print-orders.create',
                'packages.view',
                'packages.request',
                'packages.send',
                'warehouses.view',
                'routes.view',
                'distributions.view',
                'distributions.create',
                'organizations.view',
                ...$locationViews,
                ...$manualTracking,
            ],
        ];

        foreach ($roles as $roleName => $permissions) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $role->syncPermissions($permissions);
        }

        $this->seedDemoUsers();
    }

    protected function seedDemoUsers(): void
    {
        $nationalUserData = [
            'superadmin@gmail.com' => ['name' => 'Super Admin', 'access_level' => User::ACCESS_LEVEL_NATIONAL, 'role' => 'Super-Admin'],
            'admin@gmail.com' => ['name' => 'Admin User', 'access_level' => User::ACCESS_LEVEL_NATIONAL, 'role' => 'Admin'],
            'test@gmail.com' => ['name' => 'Organization Manager', 'access_level' => User::ACCESS_LEVEL_NATIONAL, 'role' => 'Org-Manager'],
        ];

        foreach ($nationalUserData as $email => $data) {
            $user = User::query()->updateOrCreate(
                ['email' => $email],
                [
                    'name' => $data['name'],
                    'password' => bcrypt('test1234'),
                    'access_level' => $data['access_level'],
                    'country_id' => null,
                    'region_id' => null,
                    'zone_id' => null,
                    'woreda_id' => null,
                    'organization_id' => null,
                    'email_verified_at' => now(),
                ]
            );

            $user->syncRoles([$data['role']]);
        }
    }
}
