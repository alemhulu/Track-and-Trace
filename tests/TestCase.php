<?php

namespace Tests;

use App\Models\User;
use App\Support\PermissionCatalog;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Users created from here on hold every module permission except managing titles,
     * which stays with admins, so a test exercises location scope and role rules alone.
     */
    protected function grantModuleAccessToNewUsers(): void
    {
        $names = array_values(array_diff(PermissionCatalog::names(), PermissionCatalog::namesOf('manual-tracking.titles')));

        foreach ($names as $name) {
            Permission::findOrCreate($name, 'web');
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        User::created(fn (User $user) => $user->givePermissionTo($names));
    }
}
