<?php

namespace Tests\Feature;

use App\Http\Livewire\ManualTracking\Shipment\CreateShipment;
use App\Http\Livewire\ManualTracking\Shipment\ShowShipment;
use App\Models\Country;
use App\Models\ManualTracking\ManualBook;
use App\Models\ManualTracking\ManualDiscrepancy;
use App\Models\ManualTracking\ManualDistribution;
use App\Models\ManualTracking\ManualStockBalance;
use App\Models\Organization;
use App\Models\Region;
use App\Models\User;
use App\Models\Zone;
use App\Services\ManualTracking\ManualShipmentService;
use App\Services\ManualTracking\ManualStockService;
use App\Services\ManualTracking\ManualTrackingException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Shipments can be counted as total copies instead of packages, for staff who find
 * counting or scanning each package impractical.
 */
class ShipmentTotalsEntryTest extends TestCase
{
    use RefreshDatabase;

    private Organization $bureau;
    private Organization $zoneOffice;
    private ManualBook $book;
    private User $national;
    private User $zoneUser;

    protected function setUp(): void
    {
        parent::setUp();

        $country = Country::query()->create(['name' => 'Ethiopia', 'code' => 'ET']);
        $region = Region::query()->create(['name' => 'Oromia', 'code' => 'OR', 'country_id' => $country->id]);
        $zone = Zone::query()->create(['name' => 'East Shewa Zone', 'region_id' => $region->id, 'country_id' => $country->id]);

        $this->bureau = Organization::query()->create(['name' => 'Oromia Education Bureau', 'country_id' => $country->id, 'region_id' => $region->id]);
        $this->zoneOffice = Organization::query()->create(['name' => 'East Shewa Zone Education Department', 'country_id' => $country->id, 'region_id' => $region->id, 'zone_id' => $zone->id]);
        $this->book = ManualBook::query()->create(['title' => 'Oromia Grade 6 Mathematics', 'region_id' => $region->id]);

        $this->national = User::factory()->create(['access_level' => User::ACCESS_LEVEL_NATIONAL]);
        $this->zoneUser = User::factory()->create(['access_level' => User::ACCESS_LEVEL_ZONE, 'region_id' => $region->id, 'zone_id' => $zone->id]);

        app(ManualStockService::class)->recordOpeningStock($this->national, $this->bureau, $this->book, ['student' => 1000, 'teacher' => 30]);
    }

    public function test_totals_are_kept_exactly_and_described_as_packages(): void
    {
        $this->assertSame(['full_packages' => 10, 'partial_package_books' => 35, 'partial_teacher_guides' => 1], $this->book->packagesFor(435, 11));

        // Fewer guides than packages cannot be written as packages, but the totals still hold
        $shipment = $this->dispatchTotals(400, 3);
        $line = $shipment->lines->first();

        $this->assertSame([400, 3, 10, 0], [$line->quantity, $line->teacher_guides, $line->full_packages, $line->partial_teacher_guides]);
        $this->assertSame(600, $this->onHand('student'));
        $this->assertSame(27, $this->onHand('teacher'));
    }

    public function test_receiving_by_totals_raises_discrepancies_for_the_difference(): void
    {
        $shipment = $this->dispatchTotals(435, 11);

        app(ManualShipmentService::class)->receive($this->zoneUser, $shipment, [
            $shipment->lines->first()->id => ['student_books' => 430, 'teacher_guides' => 11, 'damaged_quantity' => 2],
        ]);

        $this->assertSame(ManualDistribution::STATUS_RECEIVED_WITH_DISCREPANCY, $shipment->fresh()->status);
        $this->assertSame(428, $this->onHand('student', $this->zoneOffice));
        $this->assertEqualsCanonicalizing(
            [[ManualDiscrepancy::TYPE_MISSING, 5], [ManualDiscrepancy::TYPE_DAMAGED, 2]],
            ManualDiscrepancy::query()->get()->map(fn ($discrepancy) => [$discrepancy->type, (int) $discrepancy->quantity])->all()
        );
    }

    public function test_titles_without_a_teacher_guide_refuse_guide_totals(): void
    {
        $this->book->update(['has_teacher_guide' => false]);

        $this->expectException(ManualTrackingException::class);
        $this->expectExceptionMessage('has no teacher guide');
        $this->dispatchTotals(40, 1);
    }

    public function test_forms_switch_between_packages_and_totals_without_losing_numbers(): void
    {
        Livewire::actingAs($this->national)
            ->test(CreateShipment::class)
            ->set('from_organization_id', $this->bureau->id)
            ->set('to_organization_id', $this->zoneOffice->id)
            ->set('lines.0.manual_book_id', $this->book->id)
            ->set('lines.0.full_packages', 2)
            ->set('lines.0.partial_package_books', 5)
            ->call('$set', 'entryMode', 'totals')
            ->assertSet('lines.0.student_books', 85)
            ->assertSet('lines.0.teacher_guides', 2)
            ->set('lines.0.student_books', 123)
            ->set('lines.0.teacher_guides', 4)
            ->call('dispatchShipment')
            ->assertHasNoErrors();

        $shipment = ManualDistribution::query()->with('lines')->sole();
        $this->assertSame([123, 4], [$shipment->lines->first()->quantity, $shipment->lines->first()->teacher_guides]);
        $lineId = $shipment->lines->first()->id;

        Livewire::actingAs($this->zoneUser)
            ->test(ShowShipment::class, ['shipment' => $shipment->id])
            ->assertSet("counts.{$lineId}.full_packages", 3)
            ->call('$set', 'receiveMode', 'totals')
            ->assertSet("counts.{$lineId}.student_books", 123)
            ->assertSet("counts.{$lineId}.teacher_guides", 4)
            ->call('receive');

        $this->assertSame(ManualDistribution::STATUS_RECEIVED, $shipment->fresh()->status);
        $this->assertSame(4, $this->onHand('teacher', $this->zoneOffice));
    }

    private function dispatchTotals(int $studentBooks, int $teacherGuides): ManualDistribution
    {
        return app(ManualShipmentService::class)->dispatch($this->national, [
            'from_organization_id' => $this->bureau->id,
            'to_organization_id' => $this->zoneOffice->id,
            'lines' => [['manual_book_id' => $this->book->id, 'student_books' => $studentBooks, 'teacher_guides' => $teacherGuides]],
        ]);
    }

    private function onHand(string $edition, ?Organization $holder = null): int
    {
        return (int) ManualStockBalance::query()
            ->where('organization_id', ($holder ?? $this->bureau)->id)
            ->where('manual_book_id', $this->book->id)
            ->where('edition', $edition)
            ->value('on_hand');
    }
}
