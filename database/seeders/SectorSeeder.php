<?php

namespace Database\Seeders;

use App\Models\Sector;
use App\Models\User;
use Illuminate\Database\Seeder;

class SectorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ownerUser = User::query()->where('email', 'superadmin@gmail.com')->first() ?? User::query()->first();

        $sectors = [
            ['name' => 'Education Supply Chain', 'code' => 'ESC'],
            ['name' => 'Curriculum Development', 'code' => 'CRD'],
            ['name' => 'Logistics and Distribution', 'code' => 'LGD'],
        ];

        foreach ($sectors as $sector) {
            Sector::updateOrCreate(
                ['code' => $sector['code']],
                [
                    'name' => $sector['name'],
                    'user_id' => $ownerUser?->id,
                ]
            );
        }
    }
}
