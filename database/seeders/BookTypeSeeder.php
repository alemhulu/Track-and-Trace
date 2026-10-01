<?php

namespace Database\Seeders;

use App\Models\BookType;
use Illuminate\Database\Seeder;

class BookTypeSeeder extends Seeder
{
    public function run(): void
    {
        $datas = [
            ['name' => 'Student Text Book', 'code' => '0', 'description' => 'Standard student textbook'],
            ['name' => 'Teacher Guide', 'code' => '1', 'description' => 'Teacher guide edition'],
        ];

        foreach ($datas as $data) {
            BookType::firstOrCreate(['name' => $data['name']], $data);
        }
    }
}
