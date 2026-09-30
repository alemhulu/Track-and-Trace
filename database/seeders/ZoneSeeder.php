<?php

namespace Database\Seeders;

use App\Models\Region;
use App\Models\Zone;
use Illuminate\Database\Seeder;

class ZoneSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $zones = [
            ['name' => 'Bole Subcity', 'code' => 'BS01', 'is_subcity' => true, 'region' => 'Addis Ababa'],
            ['name' => 'Kirkos Subcity', 'code' => 'KS01', 'is_subcity' => true, 'region' => 'Addis Ababa'],
            ['name' => 'East Shewa Zone', 'code' => 'ESZ', 'is_subcity' => false, 'region' => 'Oromia'],
            ['name' => 'North Shewa Zone', 'code' => 'NSZ', 'is_subcity' => false, 'region' => 'Amhara'],
        ];

        foreach ($zones as $zone) {
            $region = Region::query()->where('name', $zone['region'])->first();
            if (! $region) {
                continue;
            }

            Zone::updateOrCreate(
                [
                    'name' => $zone['name'],
                    'region_id' => $region->id,
                    'country_id' => $region->country_id,
                ],
                [
                    'code' => $zone['code'],
                    'is_subcity' => $zone['is_subcity'],
                ]
            );
        }
    }
}
