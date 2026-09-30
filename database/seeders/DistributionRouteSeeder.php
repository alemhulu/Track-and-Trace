<?php

namespace Database\Seeders;

use App\Models\DistributionRoute;
use App\Models\WareHouse;
use Illuminate\Database\Seeder;

class DistributionRouteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $warehouseByBranch = WareHouse::query()->get()->keyBy('branch');

        $routes = [
            [
                'name' => 'Printer to MoE',
                'description' => 'Primary movement from central printer to ministry warehouse.',
                'from_branch' => 1101,
                'to_branch' => 1102,
            ],
            [
                'name' => 'MoE to Regional',
                'description' => 'Movement from ministry warehouse to regional bureau warehouse.',
                'from_branch' => 1102,
                'to_branch' => 1103,
            ],
            [
                'name' => 'Regional to Zone',
                'description' => 'Movement from regional warehouse to zone warehouse.',
                'from_branch' => 1103,
                'to_branch' => 1104,
            ],
            [
                'name' => 'Zone to School',
                'description' => 'Final movement from zone warehouse to school warehouse.',
                'from_branch' => 1104,
                'to_branch' => 1105,
            ],
        ];

        foreach ($routes as $route) {
            $fromWarehouse = $warehouseByBranch[$route['from_branch']] ?? null;
            $toWarehouse = $warehouseByBranch[$route['to_branch']] ?? null;

            if (! $fromWarehouse || ! $toWarehouse) {
                continue;
            }

            DistributionRoute::updateOrCreate(
                ['name' => $route['name']],
                [
                    'description' => $route['description'],
                    'from_ware_house_id' => $fromWarehouse->id,
                    'to_ware_house_id' => $toWarehouse->id,
                    'is_active' => true,
                ]
            );
        }
    }
}
