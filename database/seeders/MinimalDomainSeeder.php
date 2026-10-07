<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Country;
use App\Models\delivery as DeliveryModel;
use App\Models\Distribution;
use App\Models\DistributionRoute;
use App\Models\DistributionStep;
use App\Models\Grade;
use App\Models\Organization;
use App\Models\OrganizationType;
use App\Models\Ownership;
use App\Models\Package;
use App\Models\PrintOrder;
use App\Models\Region;
use App\Models\Sector;
use App\Models\Subject;
use App\Models\User;
use App\Models\WareHouse;
use App\Models\Woreda;
use App\Models\Zone;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MinimalDomainSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $country = Country::query()->where('name', 'Ethiopia')->first() ?? Country::query()->first();
        $region = Region::query()->where('name', 'Addis Ababa')->first() ?? Region::query()->first();
        $zone = Zone::query()->where('region_id', $region?->id)->first() ?? Zone::query()->first();
        $woreda = Woreda::query()->where('zone_id', $zone?->id)->first() ?? Woreda::query()->first();

        $user = User::updateOrCreate(
            ['email' => 'minimal.ops@track.local'],
            [
                'name' => 'Minimal Ops User',
                'password' => Hash::make('test1234'),
                'email_verified_at' => now(),
                'access_level' => User::ACCESS_LEVEL_WOREDA,
                'organization_id' => null,
                'country_id' => $country?->id,
                'region_id' => $region?->id,
                'zone_id' => $zone?->id,
                'woreda_id' => $woreda?->id,
            ]
        );

        $sector = Sector::updateOrCreate(
            ['code' => 'MIN-SEC'],
            [
                'name' => 'Minimal Sector',
                'user_id' => $user->id,
            ]
        );

        $typeMinistry = OrganizationType::query()->where('name', 'Ministry')->first();
        $typePrinter = OrganizationType::query()->where('name', 'Printer')->first();
        $ownership = Ownership::query()->where('name', 'Public')->first() ?? Ownership::query()->first();

        $moe = Organization::updateOrCreate(
            ['name' => 'Minimal Ministry'],
            [
                'email' => 'minimal.moe@track.local',
                'organization_type_id' => $typeMinistry?->id,
                'country_id' => $country?->id,
                'region_id' => $region?->id,
                'zone_id' => $zone?->id,
                'woreda_id' => $woreda?->id,
                'sector_id' => $sector->id,
                'ownership_id' => $ownership?->id,
                'assigned_user_id' => $user->id,
                'user_id' => $user->id,
                'status' => true,
            ]
        );

        $printer = Organization::updateOrCreate(
            ['name' => 'Minimal Printer'],
            [
                'email' => 'minimal.printer@track.local',
                'organization_type_id' => $typePrinter?->id,
                'country_id' => $country?->id,
                'region_id' => $region?->id,
                'zone_id' => $zone?->id,
                'woreda_id' => $woreda?->id,
                'sector_id' => $sector->id,
                'ownership_id' => $ownership?->id,
                'assigned_user_id' => $user->id,
                'user_id' => $user->id,
                'status' => true,
            ]
        );

        $ministryWarehouse = WareHouse::updateOrCreate(
            ['branch' => 2101, 'organization_id' => $moe->id],
            [
                'assigned_user_id' => $user->id,
                'country_id' => $country?->id,
                'region_id' => $region?->id,
                'zone_id' => $zone?->id,
                'woreda_id' => $woreda?->id,
            ]
        );

        $printerWarehouse = WareHouse::updateOrCreate(
            ['branch' => 2102, 'organization_id' => $printer->id],
            [
                'assigned_user_id' => $user->id,
                'country_id' => $country?->id,
                'region_id' => $region?->id,
                'zone_id' => $zone?->id,
                'woreda_id' => $woreda?->id,
            ]
        );

        $grade = Grade::query()->where('name', '10')->first() ?? Grade::query()->first();
        $subject = Subject::query()->where('name', 'Biology')->first() ?? Subject::query()->first();

        $book = Book::updateOrCreate(
            ['isbn' => '978000199999'],
            [
                'grade_id' => $grade?->id,
                'subject_id' => $subject?->id,
                'volume' => 1,
                'edition' => 1,
                'book_type' => false,
                'print_type' => 'Offset',
                'paper_size' => 'A4',
            ]
        );

        $printOrder = PrintOrder::updateOrCreate(
            [
                'book_id' => $book->id,
                'order_organization_id' => $moe->id,
                'printer_organization_id' => $printer->id,
            ],
            [
                'no_of_books' => 1000,
                'no_of_packages' => 40,
                'Book_codes' => [['batch' => 'MIN-001']],
                'print_status' => 1,
                'request_status' => 1,
                'barcode_start' => 300000000001,
                'barcode_end' => 300000001000,
                'qrcode_start' => 800000000001,
                'qrcode_end' => 800000001000,
                'book_per_package' => 25,
            ]
        );

        Package::updateOrCreate(
            [
                'print_order_id' => $printOrder->id,
                'ware_house_id' => $printerWarehouse->id,
                'step' => 1,
                'sender_organization_id' => $printer->id,
                'receiver_organization_id' => $moe->id,
            ],
            [
                'Book_codes' => [['isbn' => $book->isbn, 'count' => 1000]],
                'received' => 1000,
                'sent' => 1000,
                'balance' => 0,
                'no_of_books' => 1000,
                'books_per_package' => 25,
                'qrcode_start' => 800000000001,
                'qrcode_end' => 800000001000,
                'barcode_start' => 300000000001,
                'barcode_end' => 300000001000,
                'request_status' => true,
                'delivery_status' => true,
                'subject_id' => $subject?->id,
                'grade_id' => $grade?->id,
            ]
        );

        DeliveryModel::updateOrCreate(
            ['student_id' => 'MIN-STUDENT-001'],
            [
                'name' => 'Minimal Seed Student',
                'books' => json_encode([['isbn' => $book->isbn, 'quantity' => 1]]),
                'distributed' => 1,
            ]
        );

        $route = DistributionRoute::updateOrCreate(
            ['name' => 'Minimal Printer to Ministry'],
            [
                'description' => 'Minimal profile seeded route.',
                'from_ware_house_id' => $printerWarehouse->id,
                'to_ware_house_id' => $ministryWarehouse->id,
                'is_active' => true,
            ]
        );

        $distribution = Distribution::updateOrCreate(
            ['name' => 'Minimal Distribution'],
            [
                'description' => 'Minimal profile seeded distribution.',
                'is_active' => true,
                'printer_id' => $printer->id,
                'moe_id' => $moe->id,
                'region_id' => $region?->id,
                'zone_id' => $zone?->id,
                'woreda_id' => $woreda?->id,
                'school_id' => $moe->id,
                'step' => 1,
            ]
        );

        DistributionStep::updateOrCreate(
            [
                'distribution_id' => $distribution->id,
                'step_order' => 1,
            ],
            [
                'route_id' => $route->id,
            ]
        );

        $user->access_level = User::ACCESS_LEVEL_ORGANIZATION;
        $user->organization_id = $moe->id;
        $user->country_id = $moe->country_id;
        $user->region_id = $moe->region_id;
        $user->zone_id = $moe->zone_id;
        $user->woreda_id = $moe->woreda_id;
        $user->save();
    }
}
