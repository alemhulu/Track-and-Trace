<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\CountrySeeder;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\OrganizationTypeSeeder;
use Database\Seeders\OwnershipSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RegionSeeder;
use Database\Seeders\SectorSeeder;
use Database\Seeders\UserSeeder;
use Database\Seeders\WoredaSeeder;
use Database\Seeders\ZoneSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserAccessLevelSeedingConstraintsTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeded_users_follow_access_level_scope_constraints(): void
    {
        $this->seed([
            PermissionSeeder::class,
            CountrySeeder::class,
            RegionSeeder::class,
            ZoneSeeder::class,
            WoredaSeeder::class,
            OrganizationTypeSeeder::class,
            OwnershipSeeder::class,
            SectorSeeder::class,
            UserSeeder::class,
            OrganizationSeeder::class,
        ]);

        $this->assertScope('superadmin@gmail.com', User::ACCESS_LEVEL_NATIONAL);
        $this->assertScope('admin@gmail.com', User::ACCESS_LEVEL_NATIONAL);
        $this->assertScope('test@gmail.com', User::ACCESS_LEVEL_NATIONAL);

        $this->assertScope('regional.officer@track.local', User::ACCESS_LEVEL_REGION);
        $this->assertScope('printer.manager@track.local', User::ACCESS_LEVEL_ZONE);
        $this->assertScope('ops.manager@track.local', User::ACCESS_LEVEL_WOREDA);
        $this->assertScope('school.director@track.local', User::ACCESS_LEVEL_ORGANIZATION);
    }

    private function assertScope(string $email, string $expectedLevel): void
    {
        $user = User::query()->where('email', $email)->first();

        $this->assertNotNull($user, "User {$email} should be seeded.");
        $this->assertSame($expectedLevel, $user->access_level, "User {$email} should be {$expectedLevel}-level.");

        if ($expectedLevel === User::ACCESS_LEVEL_NATIONAL) {
            $this->assertNull($user->region_id);
            $this->assertNull($user->zone_id);
            $this->assertNull($user->woreda_id);
            $this->assertNull($user->organization_id);
            return;
        }

        if ($expectedLevel === User::ACCESS_LEVEL_REGION) {
            $this->assertNotNull($user->region_id);
            $this->assertNull($user->zone_id);
            $this->assertNull($user->woreda_id);
            $this->assertNull($user->organization_id);
            return;
        }

        if ($expectedLevel === User::ACCESS_LEVEL_ZONE) {
            $this->assertNotNull($user->region_id);
            $this->assertNotNull($user->zone_id);
            $this->assertNull($user->woreda_id);
            $this->assertNull($user->organization_id);
            return;
        }

        if ($expectedLevel === User::ACCESS_LEVEL_WOREDA) {
            $this->assertNotNull($user->region_id);
            $this->assertNotNull($user->zone_id);
            $this->assertNotNull($user->woreda_id);
            $this->assertNull($user->organization_id);
            return;
        }

        if ($expectedLevel === User::ACCESS_LEVEL_ORGANIZATION) {
            $this->assertNotNull($user->region_id);
            $this->assertNotNull($user->zone_id);
            $this->assertNotNull($user->woreda_id);
            $this->assertNotNull($user->organization_id);
        }
    }
}

