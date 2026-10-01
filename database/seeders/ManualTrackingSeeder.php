<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\ManualTracking\ManualAudit;
use App\Models\ManualTracking\ManualBook;
use App\Models\ManualTracking\ManualBookPackage;
use App\Models\ManualTracking\ManualDistribution;
use App\Models\ManualTracking\ManualDistributionLine;
use App\Models\ManualTracking\ManualStockLedger;
use App\Models\Organization;
use App\Models\Region;
use App\Models\User;
use App\Models\Woreda;
use App\Models\Zone;
use Illuminate\Database\Seeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ManualTrackingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->ensureManualTrackingSchema();

        $organizationNames = [
            'Federal Ministry of Education',
            'National School Printer',
            'Oromia Regional Education Bureau',
            'East Shewa Zone Education Office',
            'Bole Woreda Education Office',
            'Bole Primary School',
        ];

        $organizations = Organization::query()
            ->whereIn('name', $organizationNames)
            ->get()
            ->keyBy('name');

        if ($organizations->isEmpty()) {
            $organizations = $this->ensureBaselineOrganizations();
        }

        $users = User::query()
            ->whereIn('email', [
                'superadmin@gmail.com',
                'ops.manager@track.local',
                'printer.manager@track.local',
                'regional.officer@track.local',
                'school.director@track.local',
            ])
            ->get()
            ->keyBy('email');

        if ($users->isEmpty()) {
            $users = $this->ensureBaselineUsers();
        }

        $bookCatalog = [
            ['code' => 'MT-ENG-05', 'title' => 'English Grade 5', 'grade_name' => 'Grade 5', 'subject_name' => 'English', 'isbn' => '978-1-00001-0051', 'edition' => 1, 'total_copies' => 350],
            ['code' => 'MT-MATH-06', 'title' => 'Mathematics Grade 6', 'grade_name' => 'Grade 6', 'subject_name' => 'Mathematics', 'isbn' => '978-1-00001-0061', 'edition' => 2, 'total_copies' => 420],
            ['code' => 'MT-BIO-08', 'title' => 'Biology Grade 8', 'grade_name' => 'Grade 8', 'subject_name' => 'Biology', 'isbn' => '978-1-00001-0081', 'edition' => 3, 'total_copies' => 510],
            ['code' => 'MT-ICT-10', 'title' => 'ICT Grade 10', 'grade_name' => 'Grade 10', 'subject_name' => 'ICT', 'isbn' => '978-1-00001-0101', 'edition' => 2, 'total_copies' => 600],
            ['code' => 'MT-CHEM-11', 'title' => 'Chemistry Grade 11', 'grade_name' => 'Grade 11', 'subject_name' => 'Chemistry', 'isbn' => '978-1-00001-0111', 'edition' => 1, 'total_copies' => 470],
            ['code' => 'MT-GEO-12', 'title' => 'Geography Grade 12', 'grade_name' => 'Grade 12', 'subject_name' => 'Geography', 'isbn' => '978-1-00001-0121', 'edition' => 2, 'total_copies' => 435],
        ];

        $books = [];
        foreach ($bookCatalog as $bookData) {
            $book = ManualBook::query()->firstOrCreate(
                ['code' => $bookData['code']],
                [
                    'title' => $bookData['title'],
                    'grade_name' => $bookData['grade_name'],
                    'subject_name' => $bookData['subject_name'],
                    'isbn' => $bookData['isbn'],
                    'edition' => $bookData['edition'],
                    'total_copies' => $bookData['total_copies'],
                    'notes' => 'Operational manual tracking reference copy for standard distribution planning.',
                ]
            );

            $books[] = $book;
        }

        $bookPackages = [];
        foreach ($books as $book) {
            $packageCount = fake()->numberBetween(2, 3);
            $sourceOrganization = $organizations->values()->random();

            for ($i = 0; $i < $packageCount; $i++) {
                $packageCode = 'PKG-' . strtoupper(Str::slug($book->code, '-')) . '-' . ($i + 1);
                $package = ManualBookPackage::query()->firstOrCreate(
                    ['package_code' => $packageCode],
                    [
                        'manual_book_id' => $book->id,
                        'no_of_packages' => 1,
                        'books_per_package' => fake()->numberBetween(80, 220),
                        'total_books' => 0,
                        'current_balance' => 0,
                        'status' => 'available',
                        'notes' => 'Seeded stock package for ' . $book->title,
                    ]
                );

                $package->update([
                    'no_of_packages' => max(1, (int) $package->no_of_packages),
                    'books_per_package' => max(80, (int) $package->books_per_package),
                    'total_books' => max($package->total_books, (int) $package->books_per_package),
                    'current_balance' => max($package->current_balance, (int) $package->books_per_package),
                ]);

                $bookPackages[$book->id][] = $package;

                ManualStockLedger::query()->create([
                    'manual_book_id' => $book->id,
                    'manual_book_package_id' => $package->id,
                    'movement_type' => 'initial',
                    'movement_qty' => (int) $package->current_balance,
                    'balance_after' => (int) $package->current_balance,
                    'ref_type' => 'seed',
                    'ref_id' => $book->id,
                    'acted_by' => $users->get('ops.manager@track.local')?->id ?? $users->first()?->id,
                    'organization_id' => $sourceOrganization?->id,
                    'country_id' => $sourceOrganization?->country_id,
                    'region_id' => $sourceOrganization?->region_id,
                    'zone_id' => $sourceOrganization?->zone_id,
                    'woreda_id' => $sourceOrganization?->woreda_id,
                    'notes' => 'Initial operational stock seeded for ' . $book->title . ' at ' . $sourceOrganization?->name,
                ]);
            }
        }

        $distributionPlan = [
            [
                'source' => 'Bole Woreda Education Office',
                'destination' => 'Bole Primary School',
                'user' => 'ops.manager@track.local',
                'book' => 'MT-ENG-05',
                'quantity' => 45,
                'remarks' => 'Quarterly textbook issue for Bole primary schools.',
            ],
            [
                'source' => 'Bole Woreda Education Office',
                'destination' => 'Bole Primary School',
                'user' => 'ops.manager@track.local',
                'book' => 'MT-MATH-06',
                'quantity' => 55,
                'remarks' => 'Mathematics package allocation to the school network.',
            ],
            [
                'source' => 'Oromia Regional Education Bureau',
                'destination' => 'East Shewa Zone Education Office',
                'user' => 'regional.officer@track.local',
                'book' => 'MT-BIO-08',
                'quantity' => 75,
                'remarks' => 'Regional transfer of biology books to the zone office.',
            ],
            [
                'source' => 'East Shewa Zone Education Office',
                'destination' => 'Bole Woreda Education Office',
                'user' => 'regional.officer@track.local',
                'book' => 'MT-ICT-10',
                'quantity' => 30,
                'remarks' => 'Zone distribution to woreda education office for classroom use.',
            ],
            [
                'source' => 'National School Printer',
                'destination' => 'Federal Ministry of Education',
                'user' => 'printer.manager@track.local',
                'book' => 'MT-CHEM-11',
                'quantity' => 60,
                'remarks' => 'Print-run reconciliation for chemistry titles.',
            ],
            [
                'source' => 'Federal Ministry of Education',
                'destination' => 'Oromia Regional Education Bureau',
                'user' => 'superadmin@gmail.com',
                'book' => 'MT-GEO-12',
                'quantity' => 80,
                'remarks' => 'National allocation for the region education bureau.',
            ],
        ];

        foreach ($distributionPlan as $plan) {
            $sourceOrganization = $organizations[$plan['source']] ?? $organizations->first();
            $destinationOrganization = $organizations[$plan['destination']] ?? $organizations->first();
            $actor = $users[$plan['user']] ?? $users->first();
            $book = ManualBook::query()->where('code', $plan['book'])->first() ?? $books[0];
            $package = $book->packages()->orderByDesc('current_balance')->first();

            if (! $package) {
                $package = ManualBookPackage::query()->create([
                    'manual_book_id' => $book->id,
                    'package_code' => 'PKG-' . strtoupper(Str::slug($book->code, '-')) . '-SEED',
                    'no_of_packages' => 1,
                    'books_per_package' => (int) $book->total_copies,
                    'total_books' => (int) $book->total_copies,
                    'current_balance' => (int) $book->total_copies,
                    'status' => 'available',
                ]);
            }

            $quantity = min((int) $plan['quantity'], (int) $package->current_balance ?: (int) $book->total_copies);
            $balanceBefore = (int) $package->current_balance;
            $balanceAfter = max($balanceBefore - $quantity, 0);

            $distribution = ManualDistribution::query()->create([
                'reference' => 'MD-' . now()->format('YmdHis') . '-' . strtoupper(Str::random(4)),
                'distributed_by' => $actor?->id,
                'organization_id' => $sourceOrganization?->id,
                'country_id' => $sourceOrganization?->country_id,
                'region_id' => $sourceOrganization?->region_id,
                'zone_id' => $sourceOrganization?->zone_id,
                'woreda_id' => $sourceOrganization?->woreda_id,
                'destination_organization_id' => $destinationOrganization?->id,
                'destination_country_id' => $destinationOrganization?->country_id,
                'destination_region_id' => $destinationOrganization?->region_id,
                'destination_zone_id' => $destinationOrganization?->zone_id,
                'destination_woreda_id' => $destinationOrganization?->woreda_id,
                'distributed_at' => now(),
                'remarks' => $plan['remarks'],
            ]);

            $line = ManualDistributionLine::query()->create([
                'manual_distribution_id' => $distribution->id,
                'manual_book_id' => $book->id,
                'manual_book_package_id' => $package->id,
                'quantity' => $quantity,
                'source_balance_before' => $balanceBefore,
                'source_balance_after' => $balanceAfter,
            ]);

            $package->update(['current_balance' => $balanceAfter]);

            ManualStockLedger::query()->create([
                'manual_book_id' => $book->id,
                'manual_book_package_id' => $package->id,
                'movement_type' => 'outbound',
                'movement_qty' => -$quantity,
                'balance_after' => $balanceAfter,
                'ref_type' => ManualDistribution::class,
                'ref_id' => $distribution->id,
                'acted_by' => $actor?->id,
                'organization_id' => $distribution->organization_id,
                'country_id' => $distribution->country_id,
                'region_id' => $distribution->region_id,
                'zone_id' => $distribution->zone_id,
                'woreda_id' => $distribution->woreda_id,
                'notes' => $plan['remarks'],
            ]);

            ManualAudit::query()->create([
                'user_id' => $actor?->id,
                'action' => 'manual_distribution_seeded',
                'auditable_type' => ManualDistribution::class,
                'auditable_id' => $distribution->id,
                'meta' => [
                    'book_id' => $book->id,
                    'manual_distribution_line_id' => $line->id,
                    'quantity' => $quantity,
                    'source_organization' => $sourceOrganization?->name,
                    'destination_organization' => $destinationOrganization?->name,
                ],
            ]);
        }
    }

    private function ensureManualTrackingSchema(): void
    {
        if (Schema::hasTable('manual_books')) {
            return;
        }

        Schema::create('manual_books', function (Blueprint $table): void {
            $table->id();
            $table->string('code')->nullable()->unique();
            $table->string('title');
            $table->string('grade_name')->nullable();
            $table->string('subject_name')->nullable();
            $table->string('isbn')->nullable();
            $table->string('edition')->nullable();
            $table->unsignedBigInteger('total_copies')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('manual_book_packages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('manual_book_id')->constrained('manual_books')->cascadeOnDelete();
            $table->string('package_code')->unique();
            $table->unsignedInteger('no_of_packages')->default(1);
            $table->unsignedInteger('books_per_package')->default(0);
            $table->unsignedBigInteger('total_books')->default(0);
            $table->unsignedBigInteger('current_balance')->default(0);
            $table->string('status')->default('available');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('manual_distributions', function (Blueprint $table): void {
            $table->id();
            $table->string('reference')->unique();
            $table->unsignedBigInteger('distributed_by')->nullable();
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->unsignedBigInteger('country_id')->nullable();
            $table->unsignedBigInteger('region_id')->nullable();
            $table->unsignedBigInteger('zone_id')->nullable();
            $table->unsignedBigInteger('woreda_id')->nullable();
            $table->unsignedBigInteger('destination_organization_id')->nullable();
            $table->unsignedBigInteger('destination_country_id')->nullable();
            $table->unsignedBigInteger('destination_region_id')->nullable();
            $table->unsignedBigInteger('destination_zone_id')->nullable();
            $table->unsignedBigInteger('destination_woreda_id')->nullable();
            $table->timestamp('distributed_at')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        Schema::create('manual_distribution_lines', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('manual_distribution_id')->constrained('manual_distributions')->cascadeOnDelete();
            $table->foreignId('manual_book_id')->constrained('manual_books')->cascadeOnDelete();
            $table->foreignId('manual_book_package_id')->nullable()->constrained('manual_book_packages')->nullOnDelete();
            $table->unsignedBigInteger('quantity');
            $table->unsignedBigInteger('source_balance_before')->default(0);
            $table->unsignedBigInteger('source_balance_after')->default(0);
            $table->timestamps();
        });

        Schema::create('manual_stock_ledgers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('manual_book_id')->constrained('manual_books')->cascadeOnDelete();
            $table->foreignId('manual_book_package_id')->nullable()->constrained('manual_book_packages')->nullOnDelete();
            $table->string('movement_type');
            $table->bigInteger('movement_qty');
            $table->unsignedBigInteger('balance_after')->default(0);
            $table->string('ref_type')->nullable();
            $table->unsignedBigInteger('ref_id')->nullable();
            $table->unsignedBigInteger('acted_by')->nullable();
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->unsignedBigInteger('country_id')->nullable();
            $table->unsignedBigInteger('region_id')->nullable();
            $table->unsignedBigInteger('zone_id')->nullable();
            $table->unsignedBigInteger('woreda_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('manual_audits', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('action');
            $table->string('auditable_type')->nullable();
            $table->unsignedBigInteger('auditable_id')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    private function ensureBaselineOrganizations(): \Illuminate\Support\Collection
    {
        $country = Country::query()->where('name', 'Ethiopia')->first() ?? Country::query()->firstOrCreate(
            ['name' => 'Ethiopia'],
            ['code' => 'ET']
        );

        $addis = Region::query()->where('name', 'Addis Ababa')->first() ?? Region::query()->firstOrCreate(
            ['name' => 'Addis Ababa', 'country_id' => $country->id],
            ['code' => 'AA', 'is_city' => true]
        );

        $oromia = Region::query()->where('name', 'Oromia')->first() ?? Region::query()->firstOrCreate(
            ['name' => 'Oromia', 'country_id' => $country->id],
            ['code' => 'OR', 'is_city' => false]
        );

        $boleZone = Zone::query()->where('name', 'Bole Subcity')->first() ?? Zone::query()->firstOrCreate(
            ['name' => 'Bole Subcity', 'country_id' => $country->id, 'region_id' => $addis->id],
            ['code' => 'BS01', 'is_subcity' => true]
        );

        $kirkosZone = Zone::query()->where('name', 'Kirkos Subcity')->first() ?? Zone::query()->firstOrCreate(
            ['name' => 'Kirkos Subcity', 'country_id' => $country->id, 'region_id' => $addis->id],
            ['code' => 'KS01', 'is_subcity' => true]
        );

        $eastShewaZone = Zone::query()->where('name', 'East Shewa Zone')->first() ?? Zone::query()->firstOrCreate(
            ['name' => 'East Shewa Zone', 'country_id' => $country->id, 'region_id' => $oromia->id],
            ['code' => 'ESZ', 'is_subcity' => false]
        );

        $boleWoreda = Woreda::query()->where('name', 'Bole Woreda 01')->first() ?? Woreda::query()->firstOrCreate(
            ['name' => 'Bole Woreda 01', 'country_id' => $country->id, 'region_id' => $addis->id, 'zone_id' => $boleZone->id],
            ['code' => 'BW01']
        );

        $kirkosWoreda = Woreda::query()->where('name', 'Kirkos Woreda 02')->first() ?? Woreda::query()->firstOrCreate(
            ['name' => 'Kirkos Woreda 02', 'country_id' => $country->id, 'region_id' => $addis->id, 'zone_id' => $kirkosZone->id],
            ['code' => 'KW02']
        );

        $adamaWoreda = Woreda::query()->where('name', 'Adama Woreda 01')->first() ?? Woreda::query()->firstOrCreate(
            ['name' => 'Adama Woreda 01', 'country_id' => $country->id, 'region_id' => $oromia->id, 'zone_id' => $eastShewaZone->id],
            ['code' => 'AW01']
        );

        $dataset = [
            [
                'name' => 'Federal Ministry of Education',
                'country_id' => $country->id,
                'region_id' => $addis->id,
                'zone_id' => $kirkosZone->id,
                'woreda_id' => $kirkosWoreda->id,
            ],
            [
                'name' => 'National School Printer',
                'country_id' => $country->id,
                'region_id' => $addis->id,
                'zone_id' => $boleZone->id,
                'woreda_id' => $boleWoreda->id,
            ],
            [
                'name' => 'Oromia Regional Education Bureau',
                'country_id' => $country->id,
                'region_id' => $oromia->id,
                'zone_id' => $eastShewaZone->id,
                'woreda_id' => $adamaWoreda->id,
            ],
            [
                'name' => 'East Shewa Zone Education Office',
                'country_id' => $country->id,
                'region_id' => $oromia->id,
                'zone_id' => $eastShewaZone->id,
                'woreda_id' => $adamaWoreda->id,
            ],
            [
                'name' => 'Bole Woreda Education Office',
                'country_id' => $country->id,
                'region_id' => $addis->id,
                'zone_id' => $boleZone->id,
                'woreda_id' => $boleWoreda->id,
            ],
            [
                'name' => 'Bole Primary School',
                'country_id' => $country->id,
                'region_id' => $addis->id,
                'zone_id' => $boleZone->id,
                'woreda_id' => $boleWoreda->id,
            ],
        ];

        $created = new \Illuminate\Support\Collection();
        foreach ($dataset as $entry) {
            $organization = Organization::query()->firstOrCreate(
                ['name' => $entry['name']],
                [
                    'country_id' => $entry['country_id'],
                    'region_id' => $entry['region_id'],
                    'zone_id' => $entry['zone_id'],
                    'woreda_id' => $entry['woreda_id'],
                    'location' => true,
                    'status' => true,
                    'email' => strtolower(str_replace(' ', '.', $entry['name'])) . '@track.local',
                    'telephone' => '0911000000',
                ]
            );

            $created->put($entry['name'], $organization);
        }

        return $created;
    }

    private function ensureBaselineUsers(): \Illuminate\Support\Collection
    {
        $defaultUsers = [
            'superadmin@gmail.com' => ['name' => 'Super Admin', 'password' => bcrypt('test1234')],
            'ops.manager@track.local' => ['name' => 'Operations Manager', 'password' => bcrypt('test1234')],
            'printer.manager@track.local' => ['name' => 'Printer Manager', 'password' => bcrypt('test1234')],
            'regional.officer@track.local' => ['name' => 'Regional Officer', 'password' => bcrypt('test1234')],
            'school.director@track.local' => ['name' => 'School Director', 'password' => bcrypt('test1234')],
        ];

        $collection = new \Illuminate\Support\Collection();
        foreach ($defaultUsers as $email => $data) {
            $user = User::query()->firstOrCreate(
                ['email' => $email],
                [
                    'name' => $data['name'],
                    'password' => $data['password'],
                    'email_verified_at' => now(),
                ]
            );

            $collection->put($email, $user);
        }

        return $collection;
    }
}
