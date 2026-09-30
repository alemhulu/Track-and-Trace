<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\User;
use App\Models\WareHouse;
use Illuminate\Database\Seeder;

class WareHouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $organizations = Organization::query()->get()->keyBy('name');
        $users = User::query()->pluck('id', 'email');

        $rows = [
            [
                'branch' => 1101,
                'organization' => 'National School Printer',
                'user_email' => 'printer.manager@track.local',
            ],
            [
                'branch' => 1102,
                'organization' => 'Federal Ministry of Education',
                'user_email' => 'ops.manager@track.local',
            ],
            [
                'branch' => 1103,
                'organization' => 'Oromia Regional Education Bureau',
                'user_email' => 'regional.officer@track.local',
            ],
            [
                'branch' => 1104,
                'organization' => 'East Shewa Zone Education Office',
                'user_email' => 'regional.officer@track.local',
            ],
            [
                'branch' => 1105,
                'organization' => 'Bole Primary School',
                'user_email' => 'school.director@track.local',
            ],
        ];

        foreach ($rows as $row) {
            $organization = $organizations[$row['organization']] ?? null;
            if (! $organization) {
                continue;
            }

            WareHouse::updateOrCreate(
                [
                    'branch' => $row['branch'],
                    'organization_id' => $organization->id,
                ],
                [
                    'assigned_user_id' => $users[$row['user_email']] ?? null,
                    'country_id' => $organization->country_id,
                    'region_id' => $organization->region_id,
                    'zone_id' => $organization->zone_id,
                    'woreda_id' => $organization->woreda_id,
                ]
            );
        }
    }
}
