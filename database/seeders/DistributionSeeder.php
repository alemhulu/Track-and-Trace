<?php

namespace Database\Seeders;

use App\Models\Distribution;
use App\Models\Organization;
use Illuminate\Database\Seeder;

class DistributionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $printer = Organization::query()->where('name', 'National School Printer')->first();
        $moe = Organization::query()->where('name', 'Federal Ministry of Education')->first();
        $regional = Organization::query()->where('name', 'Oromia Regional Education Bureau')->first();
        $zone = Organization::query()->where('name', 'East Shewa Zone Education Office')->first();
        $school = Organization::query()->where('name', 'Bole Primary School')->first();

        $distributions = [
            [
                'name' => 'Grade 9 Science Distribution',
                'description' => 'Distribution plan for Grade 9 science book packages.',
            ],
            [
                'name' => 'Grade 10 Core Distribution',
                'description' => 'Distribution plan for Grade 10 core subject textbooks.',
            ],
        ];

        foreach ($distributions as $distribution) {
            Distribution::updateOrCreate(
                ['name' => $distribution['name']],
                [
                    'description' => $distribution['description'],
                    'is_active' => true,
                    'printer_id' => $printer?->id,
                    'moe_id' => $moe?->id,
                    'region_id' => $regional?->region_id,
                    'zone_id' => $zone?->zone_id,
                    'woreda_id' => $zone?->woreda_id,
                    'school_id' => $school?->id,
                    'step' => 4,
                ]
            );
        }
    }
}
