<?php

namespace Database\Seeders;

use App\Models\PaperSize;
use Illuminate\Database\Seeder;

class PaperSizeSeeder extends Seeder
{
    public function run(): void
    {
        $datas = [
            ['name' => 'A4', 'code' => 'A4', 'width' => 210, 'height' => 297],
            ['name' => 'A5', 'code' => 'A5', 'width' => 148, 'height' => 210],
        ];

        foreach ($datas as $data) {
            PaperSize::firstOrCreate(['name' => $data['name']], $data);
        }
    }
}
