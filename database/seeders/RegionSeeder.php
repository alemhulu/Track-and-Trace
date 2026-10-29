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
            ['name' => 'Tigray', 'code' => 'TG', 'is_city' => false],
            ['name' => 'Afar', 'code' => 'AF', 'is_city' => false],
            ['name' => 'Somali', 'code' => 'SO', 'is_city' => false],
            ['name' => 'Benishangul Gumuz', 'code' => 'BG', 'is_city' => false],
            ['name' => 'Gambella', 'code' => 'GA', 'is_city' => false],
            ['name' => 'Harari', 'code' => 'HR', 'is_city' => false],
            ['name' => 'Sidama', 'code' => 'SI', 'is_city' => false],
            ['name' => 'Central Ethiopia', 'code' => 'CE', 'is_city' => false],
            ['name' => 'South Ethiopia', 'code' => 'SE', 'is_city' => false],
            ['name' => 'South West Ethiopia', 'code' => 'SW', 'is_city' => false],
            ['name' => 'Dire Dawa', 'code' => 'DD', 'is_city' => true],
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
