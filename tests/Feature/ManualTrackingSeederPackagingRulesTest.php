<?php

namespace Tests\Feature;

use App\Models\ManualTracking\ManualBook;
use App\Models\ManualTracking\ManualBookPackage;
use App\Models\ManualTracking\ManualDistribution;
use App\Models\ManualTracking\ManualDistributionLine;
use Database\Seeders\ManualTrackingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ManualTrackingSeederPackagingRulesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (! Schema::hasColumn('woredas', 'country_id') || ! Schema::hasColumn('woredas', 'region_id')) {
            Schema::table('woredas', function (Blueprint $table): void {
                if (! Schema::hasColumn('woredas', 'country_id')) {
                    $table->unsignedBigInteger('country_id')->nullable()->after('code');
                }

                if (! Schema::hasColumn('woredas', 'region_id')) {
                    $table->unsignedBigInteger('region_id')->nullable()->after('country_id');
                }
            });
        }
    }

    public function test_seeded_packages_follow_40_book_standard_and_partial_package_rules(): void
    {
        $this->seed(ManualTrackingSeeder::class);

        $books = ManualBook::query()->with('packages')->get();
        $this->assertGreaterThan(0, $books->count(), 'Seeder should create manual books.');

        foreach ($books as $book) {
            $this->assertGreaterThan(0, $book->packages->count(), "Book {$book->id} should have at least one package.");

            $sumOfPackagedBooks = (int) $book->packages->sum('total_books');
            $this->assertSame((int) $book->total_copies, $sumOfPackagedBooks, "Book {$book->id} package totals must match total copies.");

            $partialPackages = $book->packages->filter(fn (ManualBookPackage $package): bool => (int) $package->total_books !== 40)->values();
            $this->assertLessThanOrEqual(1, $partialPackages->count(), "Book {$book->id} can have at most one partial package.");

            if ($partialPackages->isNotEmpty()) {
                $partial = $partialPackages->first();
                $this->assertGreaterThanOrEqual(1, (int) $partial->total_books);
                $this->assertLessThan(40, (int) $partial->total_books);
            }

            foreach ($book->packages as $package) {
                $this->assertSame(40, (int) $package->books_per_package, "Package {$package->id} should use the 40-book package standard.");
                $this->assertContains($package->status, ['Packed', 'In Transit', 'Distributed']);
            }
        }
    }

    public function test_seeded_distributions_are_linked_to_packages_and_destinations(): void
    {
        $this->seed(ManualTrackingSeeder::class);

        $distributions = ManualDistribution::query()->get();
        $this->assertGreaterThan(0, $distributions->count(), 'Seeder should create distribution records.');

        foreach ($distributions as $distribution) {
            $this->assertNotNull($distribution->destination_organization_id);
            $this->assertNotNull($distribution->destination_region_id);
            $this->assertNotNull($distribution->destination_zone_id);
            $this->assertNotNull($distribution->destination_woreda_id);
        }

        $lines = ManualDistributionLine::query()->get();
        $this->assertGreaterThan(0, $lines->count(), 'Seeder should create distribution lines.');

        foreach ($lines as $line) {
            $this->assertNotNull($line->manual_book_package_id);
            $this->assertGreaterThan(0, (int) $line->quantity);
            $this->assertLessThanOrEqual((int) $line->source_balance_before, (int) $line->quantity);
            $this->assertGreaterThanOrEqual(0, (int) $line->source_balance_after);
        }
    }
}
