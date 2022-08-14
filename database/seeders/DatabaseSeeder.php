<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $this->call([
            RoleSeeder::class,
            PermissionSeeder::class,
            RoleHasPermissionSeeder::class,
            AdminUserSeeder::class,
            CountrySeeder::class,
            RegionCitySeeder::class,
            ZoneSubCitySeeder::class,
            WoredaSeeder::class,
            KebeleSeeder::class,
            BookTypeSeeder::class,
            BookSeeder::class,
            OrganizationTypeSeeder::class,
            OrganizationSeeder::class,
            RouteSeeder::class,
            WareHouseSeeder::class,
            DistributionSeeder::class,
            SubjectSeeder::class,
            GradeSeeder::class,
        ]);
    }
}
