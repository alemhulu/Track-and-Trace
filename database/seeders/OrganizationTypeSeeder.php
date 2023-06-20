<?php

namespace Database\Seeders;

use App\Models\OrganizationType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class OrganizationTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            ['name' => 'School'],
            ['name' => 'Printer'],
            ['name' => 'Ware Hourse'],
        ];

        foreach ($data as $key => $value) {
            OrganizationType::create($value);
        }
    }
}
