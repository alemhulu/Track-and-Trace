<?php

namespace Database\Factories;

use App\Models\Country;
use App\Models\Organization;
use App\Models\OrganizationType;
use App\Models\Ownership;
use App\Models\Region;
use App\Models\Sector;
use App\Models\User;
use App\Models\Woreda;
use App\Models\Zone;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrganizationFactory extends Factory
{
    protected $model = Organization::class;

    public function definition()
    {
        $countryId = Country::query()->inRandomOrder()->value('id')
            ?? Country::query()->firstOrCreate(['name' => 'Factory Country'], ['code' => 'FC'])->id;

        $regionId = Region::query()->where('country_id', $countryId)->inRandomOrder()->value('id');
        if (! $regionId) {
            $regionId = Region::query()->create([
                'name' => 'Factory Region ' . $this->faker->unique()->numberBetween(1, 9999),
                'code' => 'FR' . $this->faker->numberBetween(10, 99),
                'is_city' => false,
                'country_id' => $countryId,
            ])->id;
        }

        $zoneId = Zone::query()->where('region_id', $regionId)->inRandomOrder()->value('id');
        if (! $zoneId) {
            $zoneId = Zone::query()->create([
                'name' => 'Factory Zone ' . $this->faker->unique()->numberBetween(1, 9999),
                'code' => 'FZ' . $this->faker->numberBetween(10, 99),
                'is_subcity' => false,
                'region_id' => $regionId,
                'country_id' => $countryId,
            ])->id;
        }

        $woredaId = Woreda::query()->where('zone_id', $zoneId)->inRandomOrder()->value('id');
        if (! $woredaId) {
            $woredaId = Woreda::query()->create([
                'name' => 'Factory Woreda ' . $this->faker->unique()->numberBetween(1, 9999),
                'code' => 'FW' . $this->faker->numberBetween(10, 99),
                'zone_id' => $zoneId,
            ])->id;
        }

        $organizationTypeId = OrganizationType::query()->inRandomOrder()->value('id')
            ?? OrganizationType::query()->firstOrCreate(['name' => 'School'])->id;

        $ownershipId = Ownership::query()->inRandomOrder()->value('id')
            ?? Ownership::query()->firstOrCreate(['name' => 'Public'])->id;

        $assignedUserId = User::query()->inRandomOrder()->value('id')
            ?? User::factory()->create()->id;

        $sectorId = Sector::query()->inRandomOrder()->value('id')
            ?? Sector::query()->create([
                'name' => 'Factory Sector',
                'code' => 'FAC-' . $this->faker->numberBetween(100, 999),
                'user_id' => $assignedUserId,
            ])->id;

        return [
            'name' => $this->faker->unique()->company(),
            'email' => $this->faker->unique()->companyEmail(),
            'website' => $this->faker->url(),
            'telephone' => $this->faker->numerify('09########'),
            'bank' => 'Commercial Bank of Ethiopia',
            'gps' => $this->faker->latitude() . ',' . $this->faker->longitude(),
            'year' => (int) $this->faker->year(),
            'old_code' => 'OLD-' . $this->faker->unique()->numberBetween(1000, 9999),
            'new_code' => 'NEW-' . $this->faker->unique()->numberBetween(1000, 9999),
            'facilities' => json_encode(['Office']),
            'assigned_user_id' => $assignedUserId,
            'manager_name' => $this->faker->name(),
            'phone' => $this->faker->numerify('09########'),
            'location' => true,
            'status' => true,
            'organization_type_id' => $organizationTypeId,
            'country_id' => $countryId,
            'region_id' => $regionId,
            'zone_id' => $zoneId,
            'woreda_id' => $woredaId,
            'sector_id' => $sectorId,
            'ownership_id' => $ownershipId,
            'user_id' => $assignedUserId,
        ];
    }
}
