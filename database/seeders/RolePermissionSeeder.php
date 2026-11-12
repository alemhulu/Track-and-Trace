<?php

namespace Database\Seeders;

/**
 * The permission catalog and each role's permissions, without the demo accounts.
 *
 * Safe on a live database: PermissionSeeder also creates superadmin@gmail.com and friends with
 * the test password, which must never happen in production. Run it with
 * `php artisan db:seed --class=RolePermissionSeeder --force`.
 */
class RolePermissionSeeder extends PermissionSeeder
{
    protected function seedDemoUsers(): void
    {
        // Live accounts are managed in user management
    }
}
