<?php

namespace Database\Seeders;

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
        $profile = strtolower((string) ($_ENV['SEED_PROFILE'] ?? $_SERVER['SEED_PROFILE'] ?? 'demo'));

        $this->call($this->referenceSeeders());

        if ($profile === 'minimal') {
            $this->call([
                \Database\Seeders\MinimalDomainSeeder::class,
            ]);
            return;
        }

        $this->call($this->demoDomainSeeders());
    }

    /**
     * @return array<int, class-string<Seeder>>
     */
    private function referenceSeeders(): array
    {
        return [
            \Database\Seeders\PermissionSeeder::class,
            \Database\Seeders\CountrySeeder::class,
            \Database\Seeders\RegionSeeder::class,
            \Database\Seeders\ZoneSeeder::class,
            \Database\Seeders\WoredaSeeder::class,
            \Database\Seeders\OrganizationTypeSeeder::class,
            \Database\Seeders\OwnershipSeeder::class,
            \Database\Seeders\SubjectSeeder::class,
            \Database\Seeders\GradeSeeder::class,
            \Database\Seeders\GradeSubjectSeeder::class,
        ];
    }

    /**
     * @return array<int, class-string<Seeder>>
     */
    private function demoDomainSeeders(): array
    {
        return [
            \Database\Seeders\UserSeeder::class,
            \Database\Seeders\SectorSeeder::class,
            \Database\Seeders\OrganizationSeeder::class,
            \Database\Seeders\WareHouseSeeder::class,
            \Database\Seeders\BookSeeder::class,
            \Database\Seeders\PrintOrderSeeder::class,
            \Database\Seeders\PackageSeeder::class,
            \Database\Seeders\DeliverySeeder::class,
            \Database\Seeders\DistributionRouteSeeder::class,
            \Database\Seeders\DistributionSeeder::class,
            \Database\Seeders\DistributionStepSeeder::class,
        ];
    }
}
