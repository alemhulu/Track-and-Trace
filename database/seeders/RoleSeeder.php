<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $datas = [
            ['name'=>'Super Admin','slug'=>'super-admin'],
            // ['name'=>'Student'],
            // ['name'=>'Teacher']
        ];
        foreach($datas as $data){
            Role::create($data);
        }
    }
}
