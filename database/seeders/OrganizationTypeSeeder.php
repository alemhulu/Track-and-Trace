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
        $datas = [
           [ 'name' => 'School'],
           [ 'name' => 'Printer'],
           [ 'name' => 'Ware Hourse'],
           [ 'name' => 'Ministry of Education'],
           [ 'name' => 'Regional Bureau'],
           [ 'name' => 'Zone Bureau'],
           [ 'name' => 'Woreda Bureau']
        ];
        foreach ($datas as $data) {
            OrganizationType::create($data);
        }
        
    }
}
