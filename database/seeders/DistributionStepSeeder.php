<?php

namespace Database\Seeders;

use App\Models\Distribution;
use App\Models\DistributionRoute;
use App\Models\DistributionStep;
use Illuminate\Database\Seeder;

class DistributionStepSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $routeNames = [
            'Printer to MoE',
            'MoE to Regional',
            'Regional to Zone',
            'Zone to School',
        ];

        $routeIds = DistributionRoute::query()
            ->whereIn('name', $routeNames)
            ->pluck('id', 'name');

        $distributionNames = [
            'Grade 9 Science Distribution',
            'Grade 10 Core Distribution',
        ];

        $distributions = Distribution::query()->whereIn('name', $distributionNames)->get();

        foreach ($distributions as $distribution) {
            foreach ($routeNames as $index => $routeName) {
                $routeId = $routeIds[$routeName] ?? null;
                if (! $routeId) {
                    continue;
                }

                DistributionStep::updateOrCreate(
                    [
                        'distribution_id' => $distribution->id,
                        'step_order' => $index + 1,
                    ],
                    [
                        'route_id' => $routeId,
                    ]
                );
            }
        }
    }
}
