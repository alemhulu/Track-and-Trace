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
            ['name' => 'school', 'code' => 'SH01', 'description' => 'In this section we will see how to use fake data in blade file without creating factory file. You can create fake data using fake() helper method. It is helpful to create quick prototyping a design and fake data.'],
            ['name' => 'campany', 'code' => 'CAM01', 'description' => 'In this section we will see how to use fake data in blade file without creating factory file. You can create fake data using fake() helper method. It is helpful to create quick prototyping a design and fake data.'],
            ['name' => 'organization', 'code' => 'ORG01', 'description' => 'In this section we will see how to use fake data in blade file without creating factory file. You can create fake data using fake() helper method. It is helpful to create quick prototyping a design and fake data.'],
            ['name' => 'Printer', 'code' => 'P01', 'description' => 'In this section we will see how to use fake data in blade file without creating factory file. You can create fake data using fake() helper method. It is helpful to create quick prototyping a design and fake data.'],
        ];

        foreach ($data as $key => $value) {
            OrganizationType::create($value);
        }
    }
}
