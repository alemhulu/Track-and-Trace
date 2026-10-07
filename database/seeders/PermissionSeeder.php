<?php

namespace Database\Seeders;

use App\Models\User;
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

        $permissionNames = [
            'view-dashboard',
            'view-users',
            'create-users',
            'edit-users',
            'delete-users',
            'view-roles',
            'create-roles',
            'edit-roles',
            'delete-roles',
            'view-locations',
            'manage-locations',
            'view-organizations',
            'manage-organizations',
            'view-manual-tracking',
            'view-books',
            'create-books',
            'edit-books',
            'delete-books',
            'view-print-orders',
            'create-print-orders',
            'manage-print-orders',
            'view-packages',
            'create-packages',
            'update-package-status',
            'view-warehouses',
            'manage-warehouses',
            'view-routes',
            'manage-routes',
            'view-distributions',
            'create-distributions',
            'update-distributions',
            'view-trace',
            'view-logs',
        ];

        foreach ($permissionNames as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);
        }

        $roles = [
            'Super-Admin' => array_values($permissionNames),
            'National Admin' => array_values(array_filter($permissionNames, fn ($permission) => ! in_array($permission, [
                'view-dashboard',
            ], true))),
            'Region Officer' => [
                'view-dashboard',
                'view-users',
                'view-books',
                'view-manual-tracking',
                'view-print-orders',
                'view-packages',
                'view-warehouses',
                'view-routes',
                'view-distributions',
                'view-trace',
                'view-organizations',
                'view-locations',
            ],
            'Zone Officer' => [
                'view-dashboard',
                'view-users',
                'view-books',
                'view-manual-tracking',
                'view-print-orders',
                'view-packages',
                'view-warehouses',
                'view-routes',
                'view-distributions',
                'view-trace',
                'view-organizations',
                'view-locations',
            ],
            'Woreda Officer' => [
                'view-dashboard',
                'view-users',
                'view-books',
                'view-manual-tracking',
                'view-print-orders',
                'view-packages',
                'create-packages',
                'update-package-status',
                'view-warehouses',
                'view-routes',
                'view-distributions',
                'create-distributions',
                'update-distributions',
                'view-trace',
                'view-organizations',
                'view-locations',
            ],
            'Organization User' => [
                'view-dashboard',
                'view-books',
                'view-manual-tracking',
                'view-packages',
                'view-distributions',
                'view-warehouses',
                'view-organizations',
                'view-locations',
            ],
            'School User' => [
                'view-dashboard',
                'view-books',
                'view-manual-tracking',
                'view-packages',
                'view-distributions',
                'view-warehouses',
                'view-organizations',
            ],
            'Admin' => [
                'view-dashboard',
                'view-users',
                'create-users',
                'edit-users',
                'delete-users',
                'view-roles',
                'create-roles',
                'edit-roles',
                'delete-roles',
                'view-locations',
                'manage-locations',
                'view-organizations',
                'manage-organizations',
                'view-manual-tracking',
                'view-books',
                'create-books',
                'edit-books',
                'delete-books',
                'view-print-orders',
                'create-print-orders',
                'manage-print-orders',
                'view-packages',
                'create-packages',
                'update-package-status',
                'view-warehouses',
                'manage-warehouses',
                'view-routes',
                'manage-routes',
                'view-distributions',
                'create-distributions',
                'update-distributions',
                'view-trace',
                'view-logs',
            ],
            'Org-Manager' => [
                'view-dashboard',
                'view-users',
                'view-books',
                'create-books',
                'edit-books',
                'view-manual-tracking',
                'view-print-orders',
                'create-print-orders',
                'view-packages',
                'create-packages',
                'update-package-status',
                'view-warehouses',
                'view-routes',
                'view-distributions',
                'create-distributions',
                'view-organizations',
                'view-locations',
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
