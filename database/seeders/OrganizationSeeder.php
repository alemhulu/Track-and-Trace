<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Organization;
use App\Models\OrganizationType;
use App\Models\Ownership;
use App\Models\Region;
use App\Models\Sector;
use App\Models\User;
use App\Models\Woreda;
use App\Models\Zone;
use Illuminate\Database\Seeder;

class OrganizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $country = Country::query()->where('name', 'Ethiopia')->first() ?? Country::query()->first();
        $regionAddis = Region::query()->where('name', 'Addis Ababa')->first();
        $regionOromia = Region::query()->where('name', 'Oromia')->first();
        $zoneBole = Zone::query()->where('name', 'Bole Subcity')->first();
        $zoneKirkos = Zone::query()->where('name', 'Kirkos Subcity')->first();
        $zoneEastShewa = Zone::query()->where('name', 'East Shewa Zone')->first();
        $woredaBole = Woreda::query()->where('name', 'Bole Woreda 01')->first();
        $woredaKirkos = Woreda::query()->where('name', 'Kirkos Woreda 02')->first();
        $woredaAdama = Woreda::query()->where('name', 'Adama Woreda 01')->first();

        $types = OrganizationType::query()->pluck('id', 'name');
        $ownerships = Ownership::query()->pluck('id', 'name');
        $sectors = Sector::query()->pluck('id', 'code');
        $users = User::query()->pluck('id', 'email');

        $rows = [
            [
                'name' => 'Federal Ministry of Education',
                'organization_type' => 'Ministry',
                'email' => 'moe@track.local',
                'telephone' => '0111000001',
                'manager_name' => 'Super Admin',
                'manager_phone' => '0911000001',
                'old_code' => 'MOE-OLD-001',
                'new_code' => 'MOE-NEW-001',
                'region_id' => $regionAddis?->id,
                'zone_id' => $zoneKirkos?->id,
                'woreda_id' => $woredaKirkos?->id,
                'sector_code' => 'ESC',
                'ownership' => 'Public',
                'assigned_user_email' => 'superadmin@gmail.com',
                'user_email' => 'ops.manager@track.local',
            ],
            [
                'name' => 'National School Printer',
                'organization_type' => 'Printer',
                'email' => 'printer@track.local',
                'telephone' => '0111000002',
                'manager_name' => 'Printer Manager',
                'manager_phone' => '0911000002',
                'old_code' => 'PRN-OLD-001',
                'new_code' => 'PRN-NEW-001',
                'region_id' => $regionAddis?->id,
                'zone_id' => $zoneBole?->id,
                'woreda_id' => $woredaBole?->id,
                'sector_code' => 'LGD',
                'ownership' => 'Private',
                'assigned_user_email' => 'printer.manager@track.local',
                'user_email' => 'printer.manager@track.local',
            ],
            [
                'name' => 'Oromia Regional Education Bureau',
                'organization_type' => 'Regional Bureau',
                'email' => 'oromia.reb@track.local',
                'telephone' => '0111000003',
                'manager_name' => 'Regional Officer',
                'manager_phone' => '0911000003',
                'old_code' => 'REB-OLD-001',
                'new_code' => 'REB-NEW-001',
                'region_id' => $regionOromia?->id,
                'zone_id' => $zoneEastShewa?->id,
                'woreda_id' => $woredaAdama?->id,
                'sector_code' => 'ESC',
                'ownership' => 'Public',
                'assigned_user_email' => 'regional.officer@track.local',
                'user_email' => 'regional.officer@track.local',
            ],
            [
                'name' => 'East Shewa Zone Education Office',
                'organization_type' => 'Zone Bureau',
                'email' => 'eastshewa.zone@track.local',
                'telephone' => '0111000004',
                'manager_name' => 'Zone Manager',
                'manager_phone' => '0911000005',
                'old_code' => 'ZON-OLD-001',
                'new_code' => 'ZON-NEW-001',
                'region_id' => $regionOromia?->id,
                'zone_id' => $zoneEastShewa?->id,
                'woreda_id' => $woredaAdama?->id,
                'sector_code' => 'LGD',
                'ownership' => 'Public',
                'assigned_user_email' => 'regional.officer@track.local',
                'user_email' => 'regional.officer@track.local',
            ],
            [
                'name' => 'Bole Woreda Education Office',
                'organization_type' => 'Woreda Bureau',
                'email' => 'bole.woreda@track.local',
                'telephone' => '0111000005',
                'manager_name' => 'Woreda Manager',
                'manager_phone' => '0911000006',
                'old_code' => 'WOR-OLD-001',
                'new_code' => 'WOR-NEW-001',
                'region_id' => $regionAddis?->id,
                'zone_id' => $zoneBole?->id,
                'woreda_id' => $woredaBole?->id,
                'sector_code' => 'ESC',
                'ownership' => 'Public',
                'assigned_user_email' => 'ops.manager@track.local',
                'user_email' => 'ops.manager@track.local',
            ],
            [
                'name' => 'Bole Primary School',
                'organization_type' => 'School',
                'email' => 'bole.school@track.local',
                'telephone' => '0111000006',
                'manager_name' => 'School Director',
                'manager_phone' => '0911000004',
                'old_code' => 'SCH-OLD-001',
                'new_code' => 'SCH-NEW-001',
                'region_id' => $regionAddis?->id,
                'zone_id' => $zoneBole?->id,
                'woreda_id' => $woredaBole?->id,
                'sector_code' => 'CRD',
                'ownership' => 'Public',
                'assigned_user_email' => 'school.director@track.local',
                'user_email' => 'school.director@track.local',
            ],
        ];

        foreach ($rows as $row) {
            $assignedUserId = $users[$row['assigned_user_email']] ?? null;
            $userId = $users[$row['user_email']] ?? null;

            $organization = Organization::updateOrCreate(
                ['name' => $row['name']],
                [
                    'email' => $row['email'],
                    'website' => 'https://example.com',
                    'telephone' => $row['telephone'],
                    'bank' => 'Commercial Bank of Ethiopia',
                    'gps' => '9.0000,38.0000',
                    'year' => 2024,
                    'old_code' => $row['old_code'],
                    'new_code' => $row['new_code'],
                    'facilities' => json_encode(['Warehouse', 'Office']),
                    'assigned_user_id' => $assignedUserId,
                    'manager_name' => $row['manager_name'],
                    'phone' => $row['manager_phone'],
                    'location' => true,
                    'status' => true,
                    'organization_type_id' => $types[$row['organization_type']] ?? null,
                    'country_id' => $country?->id,
                    'region_id' => $row['region_id'],
                    'zone_id' => $row['zone_id'],
                    'woreda_id' => $row['woreda_id'],
                    'sector_id' => $sectors[$row['sector_code']] ?? null,
                    'ownership_id' => $ownerships[$row['ownership']] ?? null,
                    'user_id' => $userId,
                ]
            );

            if ($assignedUserId) {
                $assignedUser = User::query()->find($assignedUserId);

                if ($assignedUser && $assignedUser->effectiveAccessLevel() === User::ACCESS_LEVEL_ORGANIZATION) {
                    User::query()->whereKey($assignedUserId)->update([
                        'organization_id' => $organization->id,
                        'country_id' => $organization->country_id,
                        'region_id' => $organization->region_id,
                        'zone_id' => $organization->zone_id,
                        'woreda_id' => $organization->woreda_id,
                    ]);
                }
            }
        }
    }
}
