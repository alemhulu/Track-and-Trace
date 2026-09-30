<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Region;
use App\Models\User;
use App\Models\Woreda;
use App\Models\Zone;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $country = Country::query()->where('name', 'Ethiopia')->first() ?? Country::query()->first();
        $addis = Region::query()->where('name', 'Addis Ababa')->first();
        $oromia = Region::query()->where('name', 'Oromia')->first();
        $boleZone = Zone::query()->where('name', 'Bole Subcity')->first();
        $eastShewaZone = Zone::query()->where('name', 'East Shewa Zone')->first();
        $boleWoreda = Woreda::query()->where('name', 'Bole Woreda 01')->first();
        $adamaWoreda = Woreda::query()->where('name', 'Adama Woreda 01')->first();

        $users = [
            [
                'name' => 'Operations Manager',
                'email' => 'ops.manager@track.local',
                'phone' => '0911000001',
                'position' => 'Operations Manager',
                'access_level' => 'woreda',
                'country_id' => $country?->id,
                'region_id' => $addis?->id,
                'zone_id' => $boleZone?->id,
                'woreda_id' => $boleWoreda?->id,
                'role' => 'Org-Manager',
            ],
            [
                'name' => 'Printer Manager',
                'email' => 'printer.manager@track.local',
                'phone' => '0911000002',
                'position' => 'Printer Manager',
                'access_level' => 'zone',
                'country_id' => $country?->id,
                'region_id' => $addis?->id,
                'zone_id' => $boleZone?->id,
                'woreda_id' => $boleWoreda?->id,
                'role' => 'Org-Manager',
            ],
            [
                'name' => 'Regional Officer',
                'email' => 'regional.officer@track.local',
                'phone' => '0911000003',
                'position' => 'Regional Officer',
                'access_level' => 'region',
                'country_id' => $country?->id,
                'region_id' => $oromia?->id,
                'zone_id' => $eastShewaZone?->id,
                'woreda_id' => $adamaWoreda?->id,
                'role' => 'Admin',
            ],
            [
                'name' => 'School Director',
                'email' => 'school.director@track.local',
                'phone' => '0911000004',
                'position' => 'School Director',
                'access_level' => 'organization',
                'country_id' => $country?->id,
                'region_id' => $addis?->id,
                'zone_id' => $boleZone?->id,
                'woreda_id' => $boleWoreda?->id,
                'role' => 'Org-Manager',
            ],
        ];

        $availableRoles = Role::query()->pluck('name')->all();

        foreach ($users as $user) {
            $model = User::updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'password' => Hash::make('test1234'),
                    'phone' => $user['phone'],
                    'position' => $user['position'],
                    'access_level' => $user['access_level'],
                    'country_id' => $user['country_id'],
                    'region_id' => $user['region_id'],
                    'zone_id' => $user['zone_id'],
                    'woreda_id' => $user['woreda_id'],
                    'email_verified_at' => now(),
                ]
            );

            if (in_array($user['role'], $availableRoles, true)) {
                $model->syncRoles([$user['role']]);
            }
        }
    }
}
