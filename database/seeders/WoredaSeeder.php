<?php

namespace Database\Seeders;

use App\Models\Woreda;
use App\Models\Zone;
use Illuminate\Database\Seeder;

class WoredaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $woredas = [
            ['name' => 'Bole Woreda 01', 'code' => 'BW01', 'zone' => 'Bole Subcity'],
            ['name' => 'Kirkos Woreda 02', 'code' => 'KW02', 'zone' => 'Kirkos Subcity'],
            ['name' => 'Adama Woreda 01', 'code' => 'AW01', 'zone' => 'East Shewa Zone'],
            ['name' => 'Debre Berhan Woreda 01', 'code' => 'DBW01', 'zone' => 'North Shewa Zone'],
        ];

        foreach ($woredas as $woreda) {
            $zone = Zone::query()->where('name', $woreda['zone'])->first();
            if (! $zone) {
                continue;
            }

            Woreda::updateOrCreate(
                [
                    'name' => $woreda['name'],
                    'zone_id' => $zone->id,
                ],
                [
                    'code' => $woreda['code'],
                ]
            );
        }
    }
}
