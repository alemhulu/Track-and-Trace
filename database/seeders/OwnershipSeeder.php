<?php

namespace Database\Seeders;

use App\Models\Ownership;
use Illuminate\Database\Seeder;

class OwnershipSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ownerships = [
            'Public',
            'Private',
            'Faith-Based',
            'NGO',
        ];

        foreach ($ownerships as $name) {
            Ownership::firstOrCreate(['name' => $name]);
        }
    }
}
