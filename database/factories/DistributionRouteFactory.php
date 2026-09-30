<?php

namespace Database\Factories;

use App\Models\DistributionRoute;
use App\Models\WareHouse;
use Illuminate\Database\Eloquent\Factories\Factory;

class DistributionRouteFactory extends Factory
{
    protected $model = DistributionRoute::class;

    public function definition()
    {
        return [
            'name' => 'Route-' . $this->faker->unique()->bothify('??-####'),
            'description' => $this->faker->sentence(),
            'from_ware_house_id' => WareHouse::factory(),
            'to_ware_house_id' => WareHouse::factory(),
            'is_active' => true,
        ];
    }
}
