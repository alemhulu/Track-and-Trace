<?php

namespace Database\Seeders;

use App\Models\PrintType;
use Illuminate\Database\Seeder;

class PrintTypeSeeder extends Seeder
{
    public function run(): void
    {
        $datas = [
            ['name' => 'Offset', 'code' => 'Offset', 'description' => 'Offset printing'],
            ['name' => 'Digital', 'code' => 'Digital', 'description' => 'Digital printing'],
        ];

        foreach ($datas as $data) {
            PrintType::firstOrCreate(['name' => $data['name']], $data);
        }
    }
}
