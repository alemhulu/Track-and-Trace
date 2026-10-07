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

        $opsUser = $users->get('ops.manager@track.local') ?? $users->first();
        $books = collect();

        for ($i = 0; $i < 14; $i++) {
            $baseCopies = fake()->numberBetween(6, 48) * 40;
            $remainder = $i % 5 === 0 ? fake()->numberBetween(1, 39) : 0;

            $books->push(
                ManualBook::factory()->create([
                    'total_copies' => $baseCopies + $remainder,
                    'notes' => 'Seeded demo textbook inventory for manual package tracking.',
                ])
            );
        }

        foreach ($books as $book) {
            $packageQuantities = $this->splitIntoStandardPackages((int) $book->total_copies, 40);
            $sourceOrganization = $organizations->values()->random();

            foreach ($packageQuantities as $index => $quantity) {
                $trackingNumber = $this->buildTrackingNumber($book->code ?? 'BOOK', $index + 1);
                $packageStatus = fake()->randomElement(['Packed', 'In Transit', 'Distributed']);

                $package = ManualBookPackage::query()->create([
                    'manual_book_id' => $book->id,
                    'package_code' => $trackingNumber,
                    'no_of_packages' => 1,
                    'books_per_package' => 40,
                    'total_books' => $quantity,
                    'current_balance' => $quantity,
                    'status' => 'Packed',
                    'notes' => sprintf('Package %d of %d for %s.', $index + 1, count($packageQuantities), $book->title),
                ]);

                ManualStockLedger::query()->create([
                    'manual_book_id' => $book->id,
                    'manual_book_package_id' => $package->id,
                    'movement_type' => 'initial',
                    'movement_qty' => $quantity,
                    'balance_after' => $quantity,
                    'ref_type' => 'seed',
                    'ref_id' => $book->id,
                    'acted_by' => $opsUser?->id,
                    'organization_id' => $sourceOrganization?->id,
                    'country_id' => $sourceOrganization?->country_id,
                    'region_id' => $sourceOrganization?->region_id,
                    'zone_id' => $sourceOrganization?->zone_id,
                    'woreda_id' => $sourceOrganization?->woreda_id,
                    'notes' => "Initial package stock for {$book->title}.",
                ]);

                if ($packageStatus === 'Packed') {
                    continue;
                }

                $destinationOrganization = $organizations
                    ->values()
                    ->where('id', '!=', $sourceOrganization?->id)
                    ->random();
                $actor = $users->values()->random();
                $quantityShipped = (int) $package->current_balance;
                $balanceBefore = (int) $package->current_balance;
                $balanceAfter = max(0, $balanceBefore - $quantityShipped);

                $distribution = ManualDistribution::query()->create([
                    'reference' => 'MD-' . strtoupper(fake()->bothify('######??')),
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
                    'distributed_at' => fake()->dateTimeBetween('-45 days', 'now'),
                    'remarks' => sprintf(
                        'Demo shipment %s from %s to %s (%s / %s / %s).',
                        $trackingNumber,
                        $sourceOrganization?->name ?? 'source office',
                        $destinationOrganization?->name ?? 'destination office',
                        $destinationOrganization?->region?->name ?? 'region',
                        $destinationOrganization?->zone?->name ?? 'zone',
                        $destinationOrganization?->woreda?->name ?? 'woreda',
                    ),
                ]);

                $distributionLine = ManualDistributionLine::query()->create([
                    'manual_distribution_id' => $distribution->id,
                    'manual_book_id' => $book->id,
                    'manual_book_package_id' => $package->id,
                    'quantity' => $quantityShipped,
                    'source_balance_before' => $balanceBefore,
                    'source_balance_after' => $balanceAfter,
                ]);

                $package->update([
                    'current_balance' => $balanceAfter,
                    'status' => $packageStatus,
                ]);

                ManualStockLedger::query()->create([
                    'manual_book_id' => $book->id,
                    'manual_book_package_id' => $package->id,
                    'movement_type' => 'outbound',
                    'movement_qty' => -$quantityShipped,
                    'balance_after' => $balanceAfter,
                    'ref_type' => ManualDistribution::class,
                    'ref_id' => $distribution->id,
                    'acted_by' => $actor?->id,
                    'organization_id' => $sourceOrganization?->id,
                    'country_id' => $sourceOrganization?->country_id,
                    'region_id' => $sourceOrganization?->region_id,
                    'zone_id' => $sourceOrganization?->zone_id,
                    'woreda_id' => $sourceOrganization?->woreda_id,
                    'notes' => "Outbound distribution for {$trackingNumber}.",
                ]);

                ManualAudit::query()->create([
                    'user_id' => $actor?->id,
                    'action' => 'manual_distribution_seeded',
                    'auditable_type' => ManualDistribution::class,
                    'auditable_id' => $distribution->id,
                    'meta' => [
                        'book_id' => $book->id,
                        'package_id' => $package->id,
                        'manual_distribution_line_id' => $distributionLine->id,
                        'tracking_number' => $trackingNumber,
                        'quantity' => $quantityShipped,
                        'source_organization' => $sourceOrganization?->name,
                        'destination_organization' => $destinationOrganization?->name,
                    ],
                ]);
            }
        }
    }

    /**
     * @return array<int, int>
     */
    private function splitIntoStandardPackages(int $totalCopies, int $standardPackageSize = 40): array
    {
        if ($totalCopies <= 0 || $standardPackageSize <= 0) {
            return [];
        }

        $fullPackages = intdiv($totalCopies, $standardPackageSize);
        $remainingCopies = $totalCopies % $standardPackageSize;

        $packages = array_fill(0, $fullPackages, $standardPackageSize);
        if ($remainingCopies > 0) {
            $packages[] = $remainingCopies;
        }

        return $packages;
    }

    private function buildTrackingNumber(string $bookCode, int $packageSequence): string
    {
        $bookSegment = strtoupper(Str::substr(preg_replace('/[^A-Za-z0-9]/', '', $bookCode), 0, 8));
        $sequenceSegment = str_pad((string) $packageSequence, 3, '0', STR_PAD_LEFT);

        return "TRK-{$bookSegment}-{$sequenceSegment}-" . strtoupper(fake()->bothify('##??'));
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
