<?php

namespace Database\Factories;

use App\Models\Country;
use App\Models\ManualTracking\ManualDistribution;
use App\Models\Organization;
use App\Models\Region;
use App\Models\User;
use App\Models\Woreda;
use App\Models\Zone;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ManualDistributionFactory extends Factory
{
    protected $model = ManualDistribution::class;

    public function definition(): array
    {
        $country = Country::query()->first() ?? Country::query()->firstOrCreate(
            ['name' => 'Factory Country'],
            ['code' => 'FC' . $this->faker->randomDigitNotZero()]
        );

        $region = Region::query()->where('country_id', $country->id)->first() ?? Region::query()->create([
            'name' => 'Factory Region ' . $this->faker->unique()->numerify('###'),
            'code' => 'FR' . $this->faker->unique()->numerify('##'),
            'country_id' => $country->id,
            'is_city' => false,
        ]);

        $zone = Zone::query()->where('region_id', $region->id)->first() ?? Zone::query()->create([
            'name' => 'Factory Zone ' . $this->faker->unique()->numerify('###'),
            'code' => 'FZ' . $this->faker->unique()->numerify('##'),
            'region_id' => $region->id,
            'country_id' => $country->id,
            'is_subcity' => false,
        ]);

        $woreda = Woreda::query()->where('zone_id', $zone->id)->first() ?? Woreda::query()->create([
            'name' => 'Factory Woreda ' . $this->faker->unique()->numerify('###'),
            'code' => 'FW' . $this->faker->unique()->numerify('##'),
            'zone_id' => $zone->id,
            'region_id' => $region->id,
            'country_id' => $country->id,
        ]);

        $organization = Organization::query()->first() ?? Organization::factory()->create([
            'country_id' => $country->id,
            'region_id' => $region->id,
            'zone_id' => $zone->id,
            'woreda_id' => $woreda->id,
        ]);

        $actor = User::query()->first() ?? User::factory()->create();

        return [
            'reference' => 'MD-' . now()->format('YmdHis') . '-' . strtoupper(Str::random(4)),
            'distributed_by' => $actor->id,
            'organization_id' => $organization->id,
            'country_id' => $country->id,
            'region_id' => $region->id,
            'zone_id' => $zone->id,
            'woreda_id' => $woreda->id,
            'destination_organization_id' => $organization->id,
            'destination_country_id' => $country->id,
            'destination_region_id' => $region->id,
            'destination_zone_id' => $zone->id,
            'destination_woreda_id' => $woreda->id,
            'distributed_at' => now(),
            'remarks' => $this->faker->sentence(8),
        ];
    }
}
