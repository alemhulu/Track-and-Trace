<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create(
            [
                'name' =>'Super Admin',
                'email' =>'admin@moe',
                'password'=>bcrypt('12345678'),
                'role_id'=>'1'
            ]
        );
    }
}