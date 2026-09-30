<?php

namespace Database\Factories;

use App\Models\Country;
use App\Models\Organization;
use App\Models\Region;
use App\Models\User;
use App\Models\WareHouse;
use App\Models\Woreda;
use App\Models\Zone;
use Illuminate\Database\Eloquent\Factories\Factory;

class WareHouseFactory extends Factory
{
    protected $model = WareHouse::class;

    public function definition()
    {
        return [
            'branch' => $this->faker->unique()->numberBetween(3000, 9999),
            'organization_id' => Organization::factory(),
            'assigned_user_id' => User::factory(),
            'country_id' => function (array $attributes) {
                $organization = Organization::query()->find($attributes['organization_id'] ?? null);
                return $organization?->country_id
                    ?? Country::query()->inRandomOrder()->value('id')
                    ?? Country::query()->firstOrCreate(['name' => 'Factory Country'], ['code' => 'FC'])->id;
            },
            'region_id' => function (array $attributes) {
                $organization = Organization::query()->find($attributes['organization_id'] ?? null);
                return $organization?->region_id
                    ?? Region::query()->where('country_id', $attributes['country_id'] ?? null)->inRandomOrder()->value('id');
            },
            'zone_id' => function (array $attributes) {
                $organization = Organization::query()->find($attributes['organization_id'] ?? null);
                return $organization?->zone_id
                    ?? Zone::query()->where('region_id', $attributes['region_id'] ?? null)->inRandomOrder()->value('id');
            },
            'woreda_id' => function (array $attributes) {
                $organization = Organization::query()->find($attributes['organization_id'] ?? null);
                return $organization?->woreda_id
                    ?? Woreda::query()->where('zone_id', $attributes['zone_id'] ?? null)->inRandomOrder()->value('id');
            },
        ];
    }
}
