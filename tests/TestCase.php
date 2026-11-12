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

    protected function setUp(): void
    {
        parent::setUp();

        // Managed academic years are cached per process; each test reads its own database
        \App\Support\AcademicYear::flush();
    }

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

    /**
     * Puts copies straight on a school's shelf. Opening stock may not be recorded at a school,
     * so tests that start from books already there place them with a plain stock movement.
     */
    protected function stockSchool(\App\Models\Organization $school, \App\Models\ManualTracking\ManualBook $book, int $student, int $teacher = 0): void
    {
        $stock = app(\App\Services\ManualTracking\ManualStockService::class);
        foreach ([\App\Models\ManualTracking\ManualBook::EDITION_STUDENT => $student, \App\Models\ManualTracking\ManualBook::EDITION_TEACHER => $teacher] as $edition => $quantity) {
            if ($quantity > 0) {
                $stock->move($school, $book, $edition, $quantity, \App\Services\ManualTracking\ManualStockService::MOVEMENT_OPENING, ['notes' => 'Test stock']);
            }
        }
    }
}
