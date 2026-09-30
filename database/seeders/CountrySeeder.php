<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $csvPath = public_path('/data/country.csv');
        if (file_exists($csvPath) && ($csvFile = fopen($csvPath, 'r')) !== false) {
            $firstline = true;
            while (($data = fgetcsv($csvFile, 2000, ',')) !== false) {
                if ($firstline) {
                    $firstline = false;
                    continue;
                }

                $name = trim((string) ($data[0] ?? ''));
                $code = trim((string) ($data[1] ?? ''));

                if ($name === '') {
                    continue;
                }

                Country::updateOrCreate(
                    ['name' => $name],
                    ['code' => $code !== '' ? $code : null]
                );
            }

            fclose($csvFile);
            return;
        }

        $fallbackCountries = [
            ['name' => 'Ethiopia', 'code' => 'ET'],
            ['name' => 'Kenya', 'code' => 'KE'],
            ['name' => 'Uganda', 'code' => 'UG'],
            ['name' => 'Rwanda', 'code' => 'RW'],
        ];

        foreach ($fallbackCountries as $country) {
            Country::updateOrCreate(
                ['name' => $country['name']],
                ['code' => $country['code']]
            );
        }
    }
}
