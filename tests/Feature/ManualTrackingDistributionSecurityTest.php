<?php

namespace Tests\Feature;

use App\Http\Livewire\ManualTracking\Distribution\AddManualDistribution;
use App\Http\Livewire\ManualTracking\Distribution\ListManualDistribution;
use App\Models\Country;
use App\Models\ManualTracking\ManualBook;
use App\Models\ManualTracking\ManualBookPackage;
use App\Models\ManualTracking\ManualDistribution;
use App\Models\ManualTracking\ManualDistributionLine;
use App\Models\Organization;
use App\Models\Region;
use App\Models\User;
use App\Models\Woreda;
use App\Models\Zone;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class ManualTrackingDistributionSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['manual_audits', 'manual_stock_ledgers', 'manual_distribution_lines', 'manual_distributions', 'manual_book_packages', 'manual_books'] as $tableName) {
            Schema::dropIfExists($tableName);
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

    public function test_valid_manual_distribution_can_be_recorded_for_authorized_user(): void
    {
        $source = $this->makeLocationHierarchy('Source');
        $destination = $this->makeLocationHierarchy('Destination');

        $user = User::factory()->create([
            'organization_id' => $source['organization']->id,
            'country_id' => $source['country']->id,
            'region_id' => $source['region']->id,
            'zone_id' => $source['zone']->id,
            'woreda_id' => $source['woreda']->id,
        ]);

        $book = ManualBook::create([
            'code' => 'MT-001',
            'title' => 'Manual Book One',
            'grade_name' => 'Grade 8',
            'subject_name' => 'Mathematics',
            'total_copies' => 25,
        ]);

        Livewire::actingAs($user)
            ->test(AddManualDistribution::class)
            ->set('manual_book_id', $book->id)
            ->set('quantity', 5)
            ->set('organization_id', $source['organization']->id)
            ->set('country_id', $source['country']->id)
            ->set('region_id', $source['region']->id)
            ->set('zone_id', $source['zone']->id)
            ->set('woreda_id', $source['woreda']->id)
            ->set('destination_organization_id', $destination['organization']->id)
            ->set('destination_country_id', $destination['country']->id)
            ->set('destination_region_id', $destination['region']->id)
            ->set('destination_zone_id', $destination['zone']->id)
            ->set('destination_woreda_id', $destination['woreda']->id)
            ->call('saveDistribution');

        $this->assertDatabaseCount('manual_distributions', 1);
        $this->assertDatabaseHas('manual_distributions', [
            'distributed_by' => $user->id,
            'organization_id' => $source['organization']->id,
            'destination_organization_id' => $destination['organization']->id,
        ]);
    }

    public function test_distribution_list_shows_subject_and_source_destination_details(): void
    {
        $source = $this->makeLocationHierarchy('Source');
        $destination = $this->makeLocationHierarchy('Destination');

        $user = User::factory()->create([
            'organization_id' => $source['organization']->id,
            'country_id' => $source['country']->id,
            'region_id' => $source['region']->id,
            'zone_id' => $source['zone']->id,
            'woreda_id' => $source['woreda']->id,
        ]);

        $book = ManualBook::create([
            'code' => 'MT-DETAILS',
            'title' => 'Detail Book',
            'grade_name' => 'Grade 9',
            'subject_name' => 'Biology',
            'total_copies' => 40,
        ]);

        $distribution = ManualDistribution::create([
            'reference' => 'MD-DETAILS-' . now()->format('YmdHis') . '-' . Str::random(4),
            'distributed_by' => $user->id,
            'organization_id' => $source['organization']->id,
            'country_id' => $source['country']->id,
            'region_id' => $source['region']->id,
            'zone_id' => $source['zone']->id,
            'woreda_id' => $source['woreda']->id,
            'destination_organization_id' => $destination['organization']->id,
            'destination_country_id' => $destination['country']->id,
            'destination_region_id' => $destination['region']->id,
            'destination_zone_id' => $destination['zone']->id,
            'destination_woreda_id' => $destination['woreda']->id,
            'distributed_at' => now(),
            'remarks' => 'Detail view check',
        ]);

        ManualDistributionLine::create([
            'manual_distribution_id' => $distribution->id,
            'manual_book_id' => $book->id,
            'manual_book_package_id' => null,
            'quantity' => 5,
            'source_balance_before' => 40,
            'source_balance_after' => 35,
        ]);

        Livewire::actingAs($user)
            ->test(ListManualDistribution::class)
            ->assertSee('Biology')
            ->assertSee($source['organization']->name)
            ->assertSee($destination['organization']->name);
    }

    public function test_manual_distribution_hierarchy_mismatch_is_rejected(): void
    {
        $source = $this->makeLocationHierarchy('Source');
        $destination = $this->makeLocationHierarchy('Destination');

        $user = User::factory()->create([
            'organization_id' => $source['organization']->id,
        ]);

        $wrongZone = Zone::create([
            'name' => 'Wrong Zone ' . Str::random(4),
            'country_id' => $destination['country']->id,
            'region_id' => $destination['region']->id,
            'code' => 'WZ' . str()->random(3),
        ]);

        $component = new AddManualDistribution();
        $method = new \ReflectionMethod(AddManualDistribution::class, 'validateAndNormalizeHierarchy');
        $method->setAccessible(true);

        $this->expectException(\RuntimeException::class);
        $method->invoke($component, [
            'manual_book_id' => 1,
            'manual_book_package_id' => null,
            'quantity' => 2,
            'organization_id' => $source['organization']->id,
            'country_id' => $source['country']->id,
            'region_id' => $source['region']->id,
            'zone_id' => $source['zone']->id,
            'woreda_id' => $source['woreda']->id,
            'destination_country_id' => $destination['country']->id,
            'destination_region_id' => $destination['region']->id,
            'destination_zone_id' => $wrongZone->id,
            'destination_woreda_id' => $destination['woreda']->id,
        ], $user);
    }

    public function test_selected_book_total_copies_are_exposed_when_a_book_is_chosen(): void
    {
        $book = ManualBook::create([
            'code' => 'MT-BOOK-TOTAL',
            'title' => 'Selected Total Book',
            'grade_name' => 'Grade 8',
            'subject_name' => 'Math',
            'total_copies' => 120,
        ]);

        $component = new \App\Http\Livewire\ManualTracking\Package\AddManualPackage();
        $component->manual_book_id = $book->id;
        $component->updatedManualBookId($book->id);

        $this->assertSame(120, $component->selectedBookTotalCopies);
    }

    public function test_manual_package_total_cannot_exceed_selected_book_total_copies(): void
    {
        $book = ManualBook::create([
            'code' => 'MT-BOOK-LIMIT',
            'title' => 'Limit Book',
            'grade_name' => 'Grade 10',
            'subject_name' => 'Science',
            'total_copies' => 120,
        ]);

        $component = new \App\Http\Livewire\ManualTracking\Package\AddManualPackage();
        $component->manual_book_id = $book->id;
        $component->updatedManualBookId($book->id);

        $this->expectException(\RuntimeException::class);

        $component->validatePackageStock(
            manualBookId: $book->id,
            noOfPackages: 2,
            booksPerPackage: 70,
        );
    }

    public function test_manual_distribution_quantity_must_not_exceed_selected_package_balance(): void
    {
        $book = ManualBook::create([
            'code' => 'MT-DIST-BOOK',
            'title' => 'Distribution Book',
            'grade_name' => 'Grade 11',
            'subject_name' => 'Biology',
            'total_copies' => 50,
        ]);

        $package = ManualBookPackage::create([
            'manual_book_id' => $book->id,
            'package_code' => 'PKG-DIST-25',
            'no_of_packages' => 1,
            'books_per_package' => 25,
            'total_books' => 25,
            'current_balance' => 25,
            'status' => 'available',
        ]);

        $component = new \App\Http\Livewire\ManualTracking\Distribution\AddManualDistribution();

        $this->expectException(\RuntimeException::class);

        $component->validateDistributionQuantity([
            'manual_book_id' => $book->id,
            'manual_book_package_id' => $package->id,
            'quantity' => 30,
        ]);
    }

    public function test_users_without_delete_permission_cannot_delete_manual_distribution(): void
    {
        $distribution = ManualDistribution::create([
            'reference' => 'MD-DELETE-' . now()->format('YmdHis') . '-' . Str::random(4),
            'distributed_by' => 99999,
            'organization_id' => null,
            'country_id' => null,
            'region_id' => null,
            'zone_id' => null,
            'woreda_id' => null,
            'destination_organization_id' => null,
            'destination_country_id' => null,
            'destination_region_id' => null,
            'destination_zone_id' => null,
            'destination_woreda_id' => null,
            'distributed_at' => now(),
            'remarks' => 'Test distribution',
        ]);

        $user = User::factory()->create([
            'access_level' => User::ACCESS_LEVEL_ORGANIZATION,
            'organization_id' => null,
        ]);

        $this->assertFalse(Gate::allows('delete', $distribution));

        Livewire::actingAs($user)
            ->test(ListManualDistribution::class)
            ->call('deleteDistribution', $distribution->id);

        $this->assertDatabaseHas('manual_distributions', ['id' => $distribution->id]);
    }

    private function makeLocationHierarchy(string $prefix): array
    {
        $suffix = Str::lower(Str::random(4));

        $country = Country::firstOrCreate(
            ['name' => $prefix . ' Country ' . $suffix],
            ['code' => strtoupper(substr($prefix, 0, 2)) . 'C' . $suffix]
        );

        $region = Region::create([
            'name' => $prefix . ' Region ' . $suffix,
            'country_id' => $country->id,
            'code' => strtoupper(substr($prefix, 0, 2)) . 'R' . $suffix,
        ]);

        $zone = Zone::create([
            'name' => $prefix . ' Zone ' . $suffix,
            'country_id' => $country->id,
            'region_id' => $region->id,
            'code' => strtoupper(substr($prefix, 0, 2)) . 'Z' . $suffix,
        ]);

        $woreda = Woreda::create([
            'name' => $prefix . ' Woreda ' . $suffix,
            'country_id' => $country->id,
            'region_id' => $region->id,
            'zone_id' => $zone->id,
            'code' => strtoupper(substr($prefix, 0, 2)) . 'W' . $suffix,
        ]);

        $organization = Organization::create([
            'name' => $prefix . ' Organization ' . $suffix,
            'country_id' => $country->id,
            'region_id' => $region->id,
            'zone_id' => $zone->id,
            'woreda_id' => $woreda->id,
        ]);

        return compact('country', 'region', 'zone', 'woreda', 'organization');
    }
}
