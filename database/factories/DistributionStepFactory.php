<?php

namespace Database\Factories;

use App\Models\Distribution;
use App\Models\DistributionRoute;
use App\Models\DistributionStep;
use Illuminate\Database\Eloquent\Factories\Factory;

class DistributionStepFactory extends Factory
{
    protected $model = DistributionStep::class;

    public function definition()
    {
        return [
            'distribution_id' => Distribution::factory(),
            'route_id' => DistributionRoute::factory(),
            'step_order' => 1,
        ];
    }
}
