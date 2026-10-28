<?php

namespace Tests\Feature;

use App\Models\Distribution;
use App\Models\ManualTracking\ManualDiscrepancy;
use App\Models\ManualTracking\ManualDistribution;
use App\Models\ManualTracking\ManualDistributionLine;
use App\Models\ManualTracking\ManualScan;
use App\Models\ManualTracking\ManualStockBalance;
use App\Models\ManualTracking\ManualStockLedger;
use App\Models\Organization;
use App\Models\Oversight\VerificationVisit;
use App\Models\Package;
use App\Models\PrintOrder;
use App\Models\School\StudentBookIssue;
use App\Models\User;
use App\Services\ManualTracking\ManualStockService;
use App\Services\School\SchoolAllocationService;
use App\Support\AcademicYear;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\Showcase\ShowcaseData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ShowcaseSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $_ENV['SEED_PROFILE'] = 'showcase';
        $this->seed(DatabaseSeeder::class);
        unset($_ENV['SEED_PROFILE']);
    }

    public function test_every_leg_conserves_quantities_and_nests_barcode_ranges(): void
    {
        $packages = Package::query()->get()->groupBy('print_order_id');
        $this->assertNotEmpty($packages);

        foreach ($packages as $orderId => $rows) {
            $order = PrintOrder::query()->find($orderId);
            $printerRow = $rows->firstWhere('step', 0);

            $this->assertNotNull($printerRow, "Order {$orderId} should start at the printer.");
            $this->assertSame((int) $order->no_of_books, (int) $printerRow->received);

            foreach ($rows as $row) {
                $this->assertSame((int) $row->received - (int) $row->sent, (int) $row->balance, "Package {$row->id} balance");
                $this->assertGreaterThanOrEqual(0, (int) $row->balance);

                $children = $rows
                    ->where('step', $row->step + 1)
                    ->where('sender_organization_id', $row->receiver_organization_id);

                if ((int) $row->sent > 0) {
                    $this->assertSame((int) $row->sent, (int) $children->sum('no_of_books'), "Package {$row->id} sent vs children");
                }

                foreach ($children as $child) {
                    $this->assertGreaterThanOrEqual((int) $row->barcode_start, (int) $child->barcode_start);
                    $this->assertLessThanOrEqual((int) $row->barcode_end, (int) $child->barcode_end);
                }
            }
        }
    }

    public function test_receipts_are_in_the_past_and_in_transit_rows_arrive_later(): void
    {
        $now = now()->addMinute();

        $this->assertSame(0, Package::query()->where('received', '>', 0)->where('created_at', '>', $now)->count());

        $inTransit = Package::query()->where('received', 0)->where('delivery_status', false)->get();
        $this->assertNotEmpty($inTransit, 'Some shipments should be in transit.');

        foreach ($inTransit as $row) {
            $this->assertTrue(now()->startOfDay()->lte($row->expected_delivery_school_time), "Package {$row->id} ETA should be upcoming.");
        }

        $this->assertSame(1, PrintOrder::query()->where('request_status', 0)->count());
        $this->assertSame(1, PrintOrder::query()->where('request_status', 4)->count());
    }

    public function test_manual_tracking_stock_reconciles_with_shipments(): void
    {
        $this->assertGreaterThan(0, ManualDistribution::query()->where('status', ManualDistribution::STATUS_DISPATCHED)->count(), 'Some shipments should be in transit.');
        $this->assertGreaterThan(0, ManualDiscrepancy::query()->where('status', ManualDiscrepancy::STATUS_OPEN)->count(), 'Some discrepancies should still be open.');

        foreach (ManualStockBalance::query()->get() as $balance) {
            $ledger = (int) ManualStockLedger::query()
                ->where('organization_id', $balance->organization_id)
                ->where('manual_book_id', $balance->manual_book_id)
                ->where('edition', $balance->edition)
                ->sum('movement_qty');
            $this->assertSame($ledger, (int) $balance->on_hand, "Balance {$balance->id} should equal its ledger");

            $inTransit = (int) ManualDistributionLine::query()
                ->where('manual_book_id', $balance->manual_book_id)
                ->whereHas('distribution', fn ($shipment) => $shipment
                    ->where('organization_id', $balance->organization_id)
                    ->where('status', ManualDistribution::STATUS_DISPATCHED))
                ->sum($balance->edition === 'teacher' ? 'teacher_guides' : 'quantity');
            $this->assertSame($inTransit, (int) $balance->in_transit_out, "Balance {$balance->id} in-transit");
        }

        // Every copy dispatched is accounted for: usable at the receiver, damaged, missing or excess
        $received = ManualDistributionLine::query()
            ->with('discrepancies')
            ->whereHas('distribution', fn ($shipment) => $shipment->where('status', '!=', ManualDistribution::STATUS_DISPATCHED))
            ->get();
        foreach ($received as $line) {
            foreach (['student' => ['quantity', 'received_quantity', 'damaged_quantity'], 'teacher' => ['teacher_guides', 'received_teacher_guides', 'damaged_teacher_guides']] as $edition => [$sent, $got, $damaged]) {
                $gap = $line->discrepancies->where('edition', $edition);
                $this->assertSame(
                    (int) $line->{$sent},
                    (int) $line->{$got} + (int) $gap->where('type', 'missing')->sum('quantity') - (int) $gap->where('type', 'excess')->sum('quantity'),
                    "Line {$line->id} {$edition} sent vs received"
                );
                $this->assertSame((int) $line->{$damaged}, (int) $gap->where('type', 'damaged')->sum('quantity'));
            }
        }

        // Each source sits inside the dispatching user's own area
        foreach (ManualDistribution::query()->with('organization')->get() as $shipment) {
            $this->assertTrue(
                app(ManualStockService::class)->canActFor(User::query()->find($shipment->distributed_by), $shipment->organization),
                "Shipment {$shipment->reference} was dispatched by someone outside the source's area"
            );
        }
    }

    public function test_school_records_match_stock_and_enrolment(): void
    {
        $issued = StudentBookIssue::query()->whereNull('returned_at')
            ->join('students', 'students.id', '=', 'student_book_issues.student_id')
            ->selectRaw('students.organization_id, student_book_issues.manual_book_id, COUNT(*) as total')
            ->groupBy('students.organization_id', 'student_book_issues.manual_book_id')
            ->get();
        $this->assertNotEmpty($issued, 'Some books should be with students.');

        foreach ($issued as $row) {
            $leftStock = -1 * (int) ManualStockLedger::query()
                ->where('organization_id', $row->organization_id)
                ->where('manual_book_id', $row->manual_book_id)
                ->whereIn('movement_type', [ManualStockService::MOVEMENT_ISSUE_STUDENT, ManualStockService::MOVEMENT_STUDENT_RETURN])
                ->sum('movement_qty');
            $this->assertSame($leftStock, (int) $row->total, "Books with students at school {$row->organization_id}");
        }

        $this->assertSame(0, StudentBookIssue::query()->whereNull('returned_at')
            ->selectRaw('student_id, manual_book_id')->groupBy('student_id', 'manual_book_id')->havingRaw('COUNT(*) > 1')->get()->count());
        $this->assertSame(0, StudentBookIssue::query()->whereNull('returned_at')
            ->whereHas('student', fn ($student) => $student->where('status', '!=', 'active'))->count(), 'Transferred students hold no books');

        $oromiaSchool = Organization::query()->where('name', 'Ifa Bula Primary School')->firstOrFail();
        $coverage = app(SchoolAllocationService::class)->forSchool($oromiaSchool, AcademicYear::current());
        $this->assertNotEmpty($coverage);
        foreach ($coverage as $row) {
            $this->assertSame($row['enrolled'], $row['required_student']);
            $this->assertGreaterThan(0.8, $row['coverage'], "{$row['book']->title} coverage");
        }
    }

    public function test_sorting_scans_match_zone_shipments_and_alerts_are_seeded(): void
    {
        // Every package on a region-to-zone shipment was scanned into that zone's bay first
        $zoneShipments = ManualDistribution::query()
            ->whereHas('organization', fn ($organization) => $organization->whereNull('zone_id')->whereNotNull('region_id'))
            ->whereHas('destinationOrganization', fn ($organization) => $organization->whereNotNull('zone_id')->whereNull('woreda_id'))
            ->with(['lines', 'destinationOrganization'])
            ->get();
        $this->assertNotEmpty($zoneShipments);

        foreach ($zoneShipments as $shipment) {
            foreach ($shipment->lines as $line) {
                $packages = $line->full_packages + ($line->partial_package_books || $line->partial_teacher_guides ? 1 : 0);
                $sorted = ManualScan::query()->live()
                    ->where('context', ManualScan::CONTEXT_SORT)
                    ->where('organization_id', $shipment->organization_id)
                    ->where('zone_id', $shipment->destinationOrganization->zone_id)
                    ->where('manual_book_id', $line->manual_book_id)
                    ->where('scanned_at', '<', $shipment->distributed_at)
                    ->count();
                $this->assertSame($packages, $sorted, "{$shipment->reference} line {$line->id}");
            }
        }

        $this->assertSame(1, ManualScan::query()->where('alert_status', ManualScan::ALERT_OPEN)->count());
        $this->assertSame(1, ManualScan::query()->where('alert_status', ManualScan::ALERT_RESOLVED)->count());
        $this->assertTrue(ManualScan::query()->where('context', ManualScan::CONTEXT_RECEIVE)
            ->whereHas('distribution', fn ($shipment) => $shipment->where('status', ManualDistribution::STATUS_DISPATCHED))->exists());
        $this->assertSame(0, ManualScan::query()->where('scanned_at', '>', now())->count());
    }

    public function test_verification_visits_and_coverage_summaries_are_consistent(): void
    {
        $allocations = app(SchoolAllocationService::class);
        $year = AcademicYear::current();
        $schools = Organization::query()->whereHas('organizationType', fn ($type) => $type->where('name', 'School'))->get();
        $summaries = $allocations->summaries($schools, $year);

        foreach ($schools as $school) {
            $this->assertSame($allocations->totals($allocations->forSchool($school, $year)), $summaries[$school->id], $school->name);
        }

        $visits = VerificationVisit::query()->get();
        $this->assertGreaterThanOrEqual(3, $visits->count());
        $this->assertSame(1, $visits->filter(fn ($visit) => $visit->stock_lines_matching < $visit->stock_lines)->count());
        $this->assertSame(1, $visits->filter(fn ($visit) => $visit->books_seen < $visit->books_expected)->count());
        $this->assertSame(0, $visits->filter(fn ($visit) => (int) $visit->visitor->organization_id === (int) $visit->organization_id)->count());
        $this->assertSame(0, $visits->filter(fn ($visit) => $visit->visited_at->isFuture())->count());
    }

    public function test_users_match_their_access_level(): void
    {
        $users = User::query()->where('email', 'like', '%@' . ShowcaseData::EMAIL_DOMAIN)->get();
        $this->assertGreaterThan(40, $users->count());

        foreach ($users as $user) {
            $this->assertNotEmpty($user->getRoleNames(), "{$user->email} should have a role");

            match ($user->access_level) {
                User::ACCESS_LEVEL_NATIONAL => $this->assertNull($user->region_id),
                User::ACCESS_LEVEL_REGION => $this->assertTrue($user->region_id && ! $user->zone_id && ! $user->organization_id),
                User::ACCESS_LEVEL_ZONE => $this->assertTrue($user->zone_id && ! $user->woreda_id && ! $user->organization_id),
                User::ACCESS_LEVEL_WOREDA => $this->assertTrue($user->woreda_id && ! $user->organization_id),
                User::ACCESS_LEVEL_ORGANIZATION => $this->assertSame((int) $user->organization->region_id, (int) $user->region_id),
            };
        }
    }

    public function test_distribution_plans_follow_connected_routes(): void
    {
        $plans = Distribution::query()->with('steps.route')->get();
        $this->assertSame(26, $plans->count());

        foreach ($plans as $plan) {
            $this->assertCount(5, $plan->steps);

            foreach ($plan->steps->values() as $index => $step) {
                if ($index > 0) {
                    $this->assertSame(
                        (int) $plan->steps[$index - 1]->route->to_ware_house_id,
                        (int) $step->route->from_ware_house_id,
                        "Plan {$plan->id} step {$step->step_order} should continue from the previous step"
                    );
                }
            }
        }
    }

    public function test_reseeding_is_a_no_op(): void
    {
        $orders = PrintOrder::query()->count();

        $_ENV['SEED_PROFILE'] = 'showcase';
        $this->seed(DatabaseSeeder::class);
        unset($_ENV['SEED_PROFILE']);

        $this->assertSame($orders, PrintOrder::query()->count());
    }
}
