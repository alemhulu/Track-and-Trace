<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Region;
use Illuminate\Database\Seeder;

class RegionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $country = Country::query()->where('name', 'Ethiopia')->first() ?? Country::query()->first();

        if (! $country) {
            $country = Country::create([
                'name' => 'Ethiopia',
                'code' => 'ET',
            ]);
        }

        $regions = [
            ['name' => 'Addis Ababa', 'code' => 'AA', 'is_city' => true],
            ['name' => 'Oromia', 'code' => 'OR', 'is_city' => false],
            ['name' => 'Amhara', 'code' => 'AM', 'is_city' => false],
        ];

        foreach ($regions as $region) {
            Region::updateOrCreate(
                [
                    'name' => $region['name'],
                    'country_id' => $country->id,
                ],
                [
                    'code' => $region['code'],
                    'is_city' => $region['is_city'],
                ]
            );
        }
    }
}
