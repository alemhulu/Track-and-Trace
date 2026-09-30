<?php

namespace Database\Factories;

use App\Models\Distribution;
use App\Models\Organization;
use App\Models\Region;
use App\Models\Woreda;
use App\Models\Zone;
use Illuminate\Database\Eloquent\Factories\Factory;

class DistributionFactory extends Factory
{
    protected $model = Distribution::class;

    public function definition()
    {
        return [
            'name' => 'Distribution-' . $this->faker->unique()->bothify('####'),
            'description' => $this->faker->sentence(),
            'is_active' => true,
            'printer_id' => Organization::factory(),
            'moe_id' => Organization::factory(),
            'region_id' => Region::query()->inRandomOrder()->value('id'),
            'zone_id' => Zone::query()->inRandomOrder()->value('id'),
            'woreda_id' => Woreda::query()->inRandomOrder()->value('id'),
            'school_id' => Organization::factory(),
            'step' => $this->faker->numberBetween(1, 4),
        ];
    }
}
