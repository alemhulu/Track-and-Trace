<?php

namespace Tests\Feature;

use App\Models\Country;
use App\Models\ManualTracking\ManualBook;
use App\Models\ManualTracking\ManualBookCode;
use App\Models\ManualTracking\ManualDiscrepancy;
use App\Models\ManualTracking\ManualDistribution;
use App\Models\ManualTracking\ManualStockBalance;
use App\Models\ManualTracking\ManualStockLedger;
use App\Models\Organization;
use App\Models\OrganizationType;
use App\Models\Region;
use App\Models\User;
use App\Models\Woreda;
use App\Models\Zone;
use App\Services\ManualTracking\ManualShipmentService;
use App\Services\ManualTracking\ManualStockService;
use App\Services\ManualTracking\ManualTrackingException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManualShipmentWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private ManualShipmentService $shipments;
    private ManualStockService $stock;
    private array $places;
    private ManualBook $book;
    private User $national;

    protected function setUp(): void
    {
        parent::setUp();

        $this->shipments = app(ManualShipmentService::class);
        $this->stock = app(ManualStockService::class);

        $country = Country::query()->create(['name' => 'Ethiopia', 'code' => 'ET']);
        $region = Region::query()->create(['name' => 'Oromia', 'code' => 'OR', 'country_id' => $country->id]);
        $zone = Zone::query()->create(['name' => 'East Shewa Zone', 'code' => 'ESZ', 'region_id' => $region->id, 'country_id' => $country->id]);
        $otherZone = Zone::query()->create(['name' => 'Jimma Zone', 'code' => 'JMZ', 'region_id' => $region->id, 'country_id' => $country->id]);
        $woreda = Woreda::query()->create(['name' => 'Adama Woreda 01', 'code' => 'AW01', 'zone_id' => $zone->id]);
        $printerType = OrganizationType::query()->create(['name' => 'Printer']);

        $org = fn (string $name, array $location, ?int $type = null) => Organization::query()->create(
            ['name' => $name, 'country_id' => $country->id, 'organization_type_id' => $type] + $location
        );

        $this->places = [
            'region' => $region,
            'zone' => $zone,
            'woreda' => $woreda,
            'printer' => $org('National Printer', ['region_id' => $region->id], $printerType->id),
            'bureau' => $org('Oromia Education Bureau', ['region_id' => $region->id]),
            'zoneOffice' => $org('East Shewa Zone Education Department', ['region_id' => $region->id, 'zone_id' => $zone->id]),
            'otherZoneOffice' => $org('Jimma Zone Education Department', ['region_id' => $region->id, 'zone_id' => $otherZone->id]),
            'school' => $org('Ifa Bula Primary School', ['region_id' => $region->id, 'zone_id' => $zone->id, 'woreda_id' => $woreda->id]),
        ];

        $this->national = User::factory()->create(['access_level' => User::ACCESS_LEVEL_NATIONAL]);

        $this->book = ManualBook::query()->create([
            'title' => 'Oromia Grade 6 Mathematics',
            'region_id' => $region->id,
            'has_teacher_guide' => true,
            'students_per_package' => 40,
        ]);
    }

    public function test_package_counts_convert_to_student_books_and_teacher_guides(): void
    {
        $this->assertSame(['student' => 400, 'teacher' => 10], $this->book->copiesFor(10));
        $this->assertSame(['student' => 435, 'teacher' => 11], $this->book->copiesFor(10, 35, 1));

        $noGuide = ManualBook::query()->create(['title' => 'Sign Language Primer', 'has_teacher_guide' => false]);
        $this->assertSame(['student' => 435, 'teacher' => 0], $noGuide->copiesFor(10, 35, 0));
    }

    public function test_dispatch_and_full_receipt_move_stock_between_holders(): void
    {
        $this->stock->recordOpeningStock($this->national, $this->places['bureau'], $this->book, ['student' => 1000, 'teacher' => 25]);

        $zoneUser = $this->zoneUser();
        $shipment = $this->shipments->dispatch($this->national, [
            'from_organization_id' => $this->places['bureau']->id,
            'to_organization_id' => $this->places['zoneOffice']->id,
            'lines' => [['manual_book_id' => $this->book->id, 'full_packages' => 10, 'partial_package_books' => 35, 'partial_teacher_guides' => 1]],
        ]);

        $this->assertSame(ManualDistribution::STATUS_DISPATCHED, $shipment->status);
        $this->assertBalance('bureau', 'student', 565, 435);
        $this->assertBalance('bureau', 'teacher', 14, 11);

        $this->shipments->receive($zoneUser, $shipment, [
            $shipment->lines->first()->id => ['full_packages' => 10, 'partial_package_books' => 35, 'partial_teacher_guides' => 1],
        ]);

        $this->assertSame(ManualDistribution::STATUS_RECEIVED, $shipment->fresh()->status);
        $this->assertBalance('bureau', 'student', 565, 0);
        $this->assertBalance('zoneOffice', 'student', 435, 0);
        $this->assertBalance('zoneOffice', 'teacher', 11, 0);
        $this->assertLedgerMatchesBalances();
    }

    public function test_cannot_dispatch_more_than_on_hand(): void
    {
        $this->stock->recordOpeningStock($this->national, $this->places['bureau'], $this->book, ['student' => 100, 'teacher' => 2]);

        $this->expectException(ManualTrackingException::class);
        $this->expectExceptionMessage('holds only 100');

        $this->shipments->dispatch($this->national, [
            'from_organization_id' => $this->places['bureau']->id,
            'to_organization_id' => $this->places['zoneOffice']->id,
            'lines' => [['manual_book_id' => $this->book->id, 'full_packages' => 3]],
        ]);
    }

    public function test_printer_is_an_external_source_without_stock(): void
    {
        $shipment = $this->shipments->dispatch($this->national, [
            'from_organization_id' => $this->places['printer']->id,
            'to_organization_id' => $this->places['bureau']->id,
            'lines' => [['manual_book_id' => $this->book->id, 'full_packages' => 5]],
        ]);

        $this->shipments->receive($this->regionUser(), $shipment, [$shipment->lines->first()->id => ['full_packages' => 5]]);

        $this->assertSame(0, ManualStockBalance::query()->where('organization_id', $this->places['printer']->id)->count());
        $this->assertBalance('bureau', 'student', 200, 0);
        $this->assertBalance('bureau', 'teacher', 5, 0);
    }

    public function test_short_damaged_and_excess_receipts_raise_discrepancies_that_resolve_into_stock(): void
    {
        $this->stock->recordOpeningStock($this->national, $this->places['bureau'], $this->book, ['student' => 400, 'teacher' => 10]);
        $shipment = $this->shipments->dispatch($this->national, [
            'from_organization_id' => $this->places['bureau']->id,
            'to_organization_id' => $this->places['zoneOffice']->id,
            'lines' => [['manual_book_id' => $this->book->id, 'full_packages' => 5]],
        ]);

        // 4 packages + 30 loose books arrived (10 missing), 6 damaged, and 1 extra teacher guide
        $this->shipments->receive($this->zoneUser(), $shipment, [
            $shipment->lines->first()->id => ['full_packages' => 4, 'partial_package_books' => 30, 'partial_teacher_guides' => 2, 'damaged_quantity' => 6],
        ]);

        $this->assertSame(ManualDistribution::STATUS_RECEIVED_WITH_DISCREPANCY, $shipment->fresh()->status);
        $this->assertBalance('zoneOffice', 'student', 184, 0);
        $this->assertBalance('zoneOffice', 'teacher', 6, 0);
        $this->assertBalance('bureau', 'student', 200, 0);

        $open = ManualDiscrepancy::query()->get()->keyBy(fn ($d) => $d->type . ':' . $d->edition);
        $this->assertSame(10, $open['missing:student']->quantity);
        $this->assertSame(6, $open['damaged:student']->quantity);
        $this->assertSame(1, $open['excess:teacher']->quantity);

        $supervisor = $this->regionUser();
        $this->shipments->resolve($supervisor, $open['missing:student'], 'found', 'Left at the woreda store.');
        $this->shipments->resolve($supervisor, $open['excess:teacher'], 'corrected');
        $this->shipments->resolve($supervisor, $open['damaged:student'], 'written_off');

        $this->assertBalance('zoneOffice', 'student', 194, 0);
        $this->assertBalance('bureau', 'teacher', 4, 0);
        $this->assertSame(0, ManualDiscrepancy::query()->where('status', ManualDiscrepancy::STATUS_OPEN)->count());
        $this->assertLedgerMatchesBalances();
    }

    public function test_receipt_requires_a_different_user_at_the_destination(): void
    {
        $this->stock->recordOpeningStock($this->national, $this->places['bureau'], $this->book, ['student' => 400, 'teacher' => 10]);
        $regionUser = $this->regionUser();
        $shipment = $this->shipments->dispatch($regionUser, [
            'from_organization_id' => $this->places['bureau']->id,
            'to_organization_id' => $this->places['zoneOffice']->id,
            'lines' => [['manual_book_id' => $this->book->id, 'full_packages' => 1]],
        ]);
        $counts = [$shipment->lines->first()->id => ['full_packages' => 1]];

        $this->assertRejected(fn () => $this->shipments->receive($regionUser, $shipment, $counts), 'cannot also confirm');

        $otherZoneUser = User::factory()->create([
            'access_level' => User::ACCESS_LEVEL_ZONE,
            'region_id' => $this->places['region']->id,
            'zone_id' => $this->places['otherZoneOffice']->zone_id,
        ]);
        $this->assertRejected(fn () => $this->shipments->receive($otherZoneUser, $shipment, $counts), 'Only users at the destination');

        $this->shipments->receive($this->zoneUser(), $shipment, $counts);
        $this->assertRejected(fn () => $this->shipments->receive($this->national, $shipment, $counts), 'already been received');
    }

    public function test_dispatch_source_must_be_in_the_senders_area(): void
    {
        $this->assertRejected(fn () => $this->shipments->dispatch($this->zoneUser(), [
            'from_organization_id' => $this->places['bureau']->id,
            'to_organization_id' => $this->places['school']->id,
            'lines' => [['manual_book_id' => $this->book->id, 'full_packages' => 1]],
        ]), 'only dispatch from organizations in your area');
    }

    public function test_sender_and_receiver_cannot_resolve_their_own_discrepancy(): void
    {
        $this->stock->recordOpeningStock($this->national, $this->places['bureau'], $this->book, ['student' => 400, 'teacher' => 10]);
        $shipment = $this->shipments->dispatch($this->national, [
            'from_organization_id' => $this->places['bureau']->id,
            'to_organization_id' => $this->places['zoneOffice']->id,
            'lines' => [['manual_book_id' => $this->book->id, 'full_packages' => 2]],
        ]);
        $zoneUser = $this->zoneUser();
        $this->shipments->receive($zoneUser, $shipment, [$shipment->lines->first()->id => ['full_packages' => 1]]);
        $discrepancy = ManualDiscrepancy::query()->where('edition', 'student')->firstOrFail();

        $this->assertRejected(fn () => $this->shipments->resolve($zoneUser, $discrepancy, 'written_off'), 'someone other than');
        $this->assertRejected(fn () => $this->shipments->resolve($this->national, $discrepancy, 'corrected'), 'does not apply');

        $this->shipments->resolve($this->regionUser(), $discrepancy, 'written_off');
        $this->assertSame('written_off', $discrepancy->fresh()->resolution);
    }

    public function test_dispatch_with_the_same_client_uuid_is_recorded_once(): void
    {
        $this->stock->recordOpeningStock($this->national, $this->places['bureau'], $this->book, ['student' => 400, 'teacher' => 10]);
        $payload = [
            'from_organization_id' => $this->places['bureau']->id,
            'to_organization_id' => $this->places['zoneOffice']->id,
            'lines' => [['manual_book_id' => $this->book->id, 'full_packages' => 2]],
            'client_uuid' => '7d1c5a0e-3f2b-4c8e-9a61-2b7f0d4e5c11',
        ];

        $first = $this->shipments->dispatch($this->national, $payload);
        $second = $this->shipments->dispatch($this->national, $payload);

        $this->assertTrue($first->is($second));
        $this->assertBalance('bureau', 'student', 320, 80);
    }

    public function test_opening_stock_is_limited_to_national_and_regional_users(): void
    {
        $this->assertRejected(
            fn () => $this->stock->recordOpeningStock($this->zoneUser(), $this->places['zoneOffice'], $this->book, ['student' => 40]),
            'Only national or regional users'
        );

        $this->stock->recordOpeningStock($this->regionUser(), $this->places['zoneOffice'], $this->book, ['student' => 40]);
        $this->assertBalance('zoneOffice', 'student', 40, 0);
    }

    public function test_scanned_qr_text_matches_codes_regardless_of_case_and_spacing(): void
    {
        ManualBookCode::query()->create([
            'manual_book_id' => $this->book->id,
            'edition' => 'teacher',
            'payload' => 'Oromia Grade 6 Mathematics Teacher guide',
            'print_code' => '25110-014-1-1709',
        ]);

        $this->assertSame('teacher', ManualBookCode::findByScan('  oromia  grade 6 MATHEMATICS teacher Guide ')?->edition);
        $this->assertSame($this->book->id, ManualBookCode::findByScan('25110-014-1-1709')?->manual_book_id);
        $this->assertNull(ManualBookCode::findByScan('Amhara Grade 6 Mathematics Teacher guide'));
    }

    private function zoneUser(): User
    {
        return User::factory()->create([
            'access_level' => User::ACCESS_LEVEL_ZONE,
            'region_id' => $this->places['region']->id,
            'zone_id' => $this->places['zone']->id,
        ]);
    }

    private function regionUser(): User
    {
        return User::factory()->create([
            'access_level' => User::ACCESS_LEVEL_REGION,
            'region_id' => $this->places['region']->id,
        ]);
    }

    private function assertBalance(string $place, string $edition, int $onHand, int $inTransit): void
    {
        $balance = ManualStockBalance::query()
            ->where('organization_id', $this->places[$place]->id)
            ->where('manual_book_id', $this->book->id)
            ->where('edition', $edition)
            ->first();

        $this->assertSame([$onHand, $inTransit], [(int) $balance?->on_hand, (int) $balance?->in_transit_out], "{$place} {$edition} balance");
    }

    private function assertLedgerMatchesBalances(): void
    {
        foreach (ManualStockBalance::query()->get() as $balance) {
            $ledger = (int) ManualStockLedger::query()
                ->where('organization_id', $balance->organization_id)
                ->where('manual_book_id', $balance->manual_book_id)
                ->where('edition', $balance->edition)
                ->sum('movement_qty');

            $this->assertSame($ledger, (int) $balance->on_hand);
        }
    }

    private function assertRejected(callable $action, string $message): void
    {
        try {
            $action();
            $this->fail("Expected rejection: {$message}");
        } catch (ManualTrackingException $exception) {
            $this->assertStringContainsString($message, $exception->getMessage());
        }
    }
}
