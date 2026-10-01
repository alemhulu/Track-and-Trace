<?php

namespace Database\Factories\ManualTracking;

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
        $country = Country::query()->first();
        $regionNames = ['Addis Ababa', 'Oromia'];
        $region = $country ? Region::query()->where('country_id', $country->id)->whereIn('name', $regionNames)->inRandomOrder()->first() : Region::query()->inRandomOrder()->first();

        $zone = $region ? Zone::query()->where('region_id', $region->id)->inRandomOrder()->first() : Zone::query()->inRandomOrder()->first();
        $woreda = $zone ? Woreda::query()->where('zone_id', $zone->id)->inRandomOrder()->first() : Woreda::query()->inRandomOrder()->first();

        $organization = Organization::query()->whereNotNull('region_id')->whereNotNull('zone_id')->inRandomOrder()->first();
        $destinationOrganization = Organization::query()->whereNotNull('region_id')->whereNotNull('zone_id')->whereKeyNot($organization?->id)->inRandomOrder()->first() ?? $organization;

        $actor = User::query()->whereIn('email', ['ops.manager@track.local', 'regional.officer@track.local', 'school.director@track.local'])->inRandomOrder()->first() ?? User::query()->first();

        return [
            'reference' => 'MD-' . now()->format('YmdHis') . '-' . strtoupper(Str::random(4)),
            'distributed_by' => $actor?->id,
            'organization_id' => $organization?->id,
            'country_id' => $organization?->country_id ?? $country?->id,
            'region_id' => $organization?->region_id ?? $region?->id,
            'zone_id' => $organization?->zone_id ?? $zone?->id,
            'woreda_id' => $organization?->woreda_id ?? $woreda?->id,
            'destination_organization_id' => $destinationOrganization?->id,
            'destination_country_id' => $destinationOrganization?->country_id ?? $country?->id,
            'destination_region_id' => $destinationOrganization?->region_id ?? $region?->id,
            'destination_zone_id' => $destinationOrganization?->zone_id ?? $zone?->id,
            'destination_woreda_id' => $destinationOrganization?->woreda_id ?? $woreda?->id,
            'distributed_at' => now(),
            'remarks' => $this->faker->sentence(8),
        ];
    }
}
