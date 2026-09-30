<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
    public function run()
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissionNames = [
            'user-list',
            'user-create',
            'user-edit',
            'user-delete',
            'view-user',
            'org-list',
            'org-create',
            'org-edit',
            'org-update',
            'org-delete',
            'org-publish',
            'org-unpublish',
            'role-list',
            'view-role',
            'role-create',
            'role-edit',
            'role-delete',
            'location-list',
            'location-create',
            'location-edit',
            'location-delete',
            'book-list',
            'book-show',
            'book-edit',
            'book-update',
            'book-delete',
            'view-logs',
        ];

        foreach ($permissionNames as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName]);
        }

        $role1 = Role::create(['name' => 'Org-Manager']);
        $role1->givePermissionTo(['book-list', 'book-show', 'org-edit', 'org-update', 'view-user']);

        $role2 = Role::create(['name' => 'Admin']);
        $role2->givePermissionTo(['role-list', 'role-create', 'role-edit', 'role-delete', 'view-role', 'user-list', 'user-create', 'user-edit', 'user-delete']);

        $role3 = Role::create(['name' => 'Super-Admin']);
        // gets all permissions via Gate::before rule; see AuthServiceProvider

        // create demo users
        //superadmin
        $user = \App\Models\User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'superadmin@gmail.com',
            'password' => bcrypt('test1234'),
        ]);
        $user->assignRole($role3);

        // create a new user1
        $user = \App\Models\User::factory()->create([
            'name' => 'Organization Manager',
            'email' => 'test@gmail.com',
            'password' => bcrypt('test1234'),
        ]);
        $user->assignRole($role1);

        // create a new admin user2
        $user = \App\Models\User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('test1234'),
        ]);
        $user->assignRole($role2);
    }
}
