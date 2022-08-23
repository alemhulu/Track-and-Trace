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

         // User permissions
         Permission::create(['name' => 'user-list']);
         Permission::create(['name' => 'user-create']);
         Permission::create(['name' => 'user-edit']);
         Permission::create(['name' => 'user-delete']);

        // org permissions
        Permission::create(['name' => 'org-list']);
        Permission::create(['name' => 'org-create']);
        Permission::create(['name' => 'org-edit']);
        Permission::create(['name' => 'org-update']);
        Permission::create(['name' => 'org-delete']);

        Permission::create(['name' => 'org-publish']);
        Permission::create(['name' => 'org-unpublish']);

          // Role permissions
          Permission::create(['name' => 'role-list']);
          Permission::create(['name' => 'role-create']);
          Permission::create(['name' => 'role-edit']);
          Permission::create(['name' => 'role-delete']);

            // Settings permissions
            Permission::create(['name' => 'location-list']);
            Permission::create(['name' => 'location-create']);
            Permission::create(['name' => 'location-edit']);
            Permission::create(['name' => 'location-delete']);

             // Contact messages permissions
            Permission::create(['name' => 'book-list']);
            Permission::create(['name' => 'book-show']);
            Permission::create(['name' => 'book-edit']);
            Permission::create(['name' => 'book-update']);    
            Permission::create(['name' => 'book-delete']);

        // create roles and assign existing permissions
        $role1 = Role::create(['name' => 'Org-Manager']);
        $role1->givePermissionTo('book-list');
        $role1->givePermissionTo('book-show');
        $role1->givePermissionTo('org-edit');
        $role1->givePermissionTo('org-update');

        $role2 = Role::create(['name' => 'Admin']);
        $role2->givePermissionTo('role-list');
        $role2->givePermissionTo('role-create');
        $role2->givePermissionTo('role-edit');
        $role2->givePermissionTo('role-delete');

        $role3 = Role::create(['name' => 'Super-Admin']);
        // gets all permissions via Gate::before rule; see AuthServiceProvider

        // create demo users
        //superadmin
        $user = \App\Models\User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'superadmin@gmail.com',
            'password'=>bcrypt('test1234'),
        ]);
        $user->assignRole($role3);

        // create a new user1
        $user = \App\Models\User::factory()->create([
            'name' => 'Organization Manager',
            'email' => 'test@gmail.com',
            'password'=>bcrypt('test1234'),
        ]);
        $user->assignRole($role1);

        // create a new admin user2
        $user = \App\Models\User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@gmail.com',
            'password'=>bcrypt('test1234'),
        ]);
        $user->assignRole($role2);
    }
}
