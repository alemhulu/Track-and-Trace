<?php

namespace Tests\Feature;

use App\Http\Livewire\ManualTracking\Scan\ScanAlertIndex;
use App\Http\Livewire\ManualTracking\Scan\SortPackages;
use App\Http\Livewire\ManualTracking\Shipment\CreateShipment;
use App\Http\Livewire\ManualTracking\Shipment\ShowShipment;
use App\Http\Livewire\School\IssueBooks;
use App\Models\Country;
use App\Models\Grade;
use App\Models\ManualTracking\ManualBook;
use App\Models\ManualTracking\ManualBookCode;
use App\Models\ManualTracking\ManualDistribution;
use App\Models\ManualTracking\ManualScan;
use App\Models\Organization;
use App\Models\Region;
use App\Models\School\Student;
use App\Models\School\StudentBookIssue;
use App\Models\User;
use App\Models\Woreda;
use App\Models\Zone;
use App\Services\ManualTracking\ManualScanService;
use App\Services\ManualTracking\ManualShipmentService;
use App\Services\ManualTracking\ManualStockService;
use App\Services\ManualTracking\ManualTrackingException;
use App\Support\AcademicYear;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ManualScanWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private ManualScanService $scanner;
    private array $places;
    private ManualBook $math;
    private ManualBook $amharaMath;
    private User $national;

    protected function setUp(): void
    {
        parent::setUp();
        $this->grantModuleAccessToNewUsers();

        $this->scanner = app(ManualScanService::class);

        $country = Country::query()->create(['name' => 'Ethiopia', 'code' => 'ET']);
        $oromia = Region::query()->create(['name' => 'Oromia', 'code' => 'OR', 'country_id' => $country->id]);
        $amhara = Region::query()->create(['name' => 'Amhara', 'code' => 'AM', 'country_id' => $country->id]);
        $eastShewa = Zone::query()->create(['name' => 'East Shewa Zone', 'region_id' => $oromia->id, 'country_id' => $country->id]);
        $jimma = Zone::query()->create(['name' => 'Jimma Zone', 'region_id' => $oromia->id, 'country_id' => $country->id]);
        $northShewaOromia = Zone::query()->create(['name' => 'North Shewa Zone', 'region_id' => $oromia->id, 'country_id' => $country->id]);
        $northShewaAmhara = Zone::query()->create(['name' => 'North Shewa Zone', 'region_id' => $amhara->id, 'country_id' => $country->id]);
        $woreda = Woreda::query()->create(['name' => 'Adama Woreda 01', 'zone_id' => $eastShewa->id]);

        $org = fn (string $name, array $location) => Organization::query()->create(['name' => $name, 'country_id' => $country->id] + $location);

        $this->places = [
            'oromia' => $oromia,
            'amhara' => $amhara,
            'eastShewa' => $eastShewa,
            'jimma' => $jimma,
            'northShewaOromia' => $northShewaOromia,
            'northShewaAmhara' => $northShewaAmhara,
            'moe' => $org('MoE Central Warehouse', []),
            'bureau' => $org('Oromia Education Bureau', ['region_id' => $oromia->id]),
            'zoneOffice' => $org('East Shewa Zone Education Department', ['region_id' => $oromia->id, 'zone_id' => $eastShewa->id]),
            'school' => $org('Ifa Bula Primary School', ['region_id' => $oromia->id, 'zone_id' => $eastShewa->id, 'woreda_id' => $woreda->id]),
        ];

        $this->national = User::factory()->create(['access_level' => User::ACCESS_LEVEL_NATIONAL]);

        $grade = Grade::query()->create(['name' => '6']);
        $this->math = ManualBook::query()->create(['title' => 'Oromia Grade 6 Mathematics', 'region_id' => $oromia->id, 'grade_id' => $grade->id]);
        $this->amharaMath = ManualBook::query()->create(['title' => 'Amhara Grade 6 Mathematics', 'region_id' => $amhara->id, 'grade_id' => $grade->id]);

        foreach ([[$this->math, '25110-014-1-1709'], [$this->amharaMath, '25120-014-1-1709']] as [$book, $printCode]) {
            ManualBookCode::query()->create(['manual_book_id' => $book->id, 'edition' => 'student', 'payload' => $book->title . ' Student Textbook', 'print_code' => $printCode]);
            ManualBookCode::query()->create(['manual_book_id' => $book->id, 'edition' => 'teacher', 'payload' => $book->title . ' Teacher guide']);
        }
    }

    public function test_scans_resolve_to_titles_and_zones_whatever_the_spelling(): void
    {
        $byText = $this->scanner->identify('  oromia grade 6   MATHEMATICS teacher GUIDE ');
        $this->assertSame([ManualScan::KIND_BOOK, $this->math->id, 'teacher'], [$byText['kind'], $byText['book']->id, $byText['edition']]);

        $byPrintCode = $this->scanner->identify('25110-014-1-1709');
        $this->assertSame([$this->math->id, 'student'], [$byPrintCode['book']->id, $byPrintCode['edition']]);

        foreach (['EAST SHEWA', 'East Shewa Zone', 'east-shewa'] as $label) {
            $this->assertSame($this->places['eastShewa']->id, $this->scanner->identify($label)['zone']?->id, $label);
        }

        $unknown = $this->scanner->identify('BATCH 7781');
        $this->assertSame([ManualScan::KIND_UNKNOWN, ManualScan::FLAG_UNKNOWN], [$unknown['kind'], $unknown['flag']]);
    }

    public function test_zone_names_shared_by_two_regions_resolve_by_where_the_scan_is_made(): void
    {
        $atBureau = $this->scanner->identify('NORTH SHEWA', $this->places['bureau']);
        $this->assertSame($this->places['northShewaOromia']->id, $atBureau['zone']->id);
        $this->assertNull($atBureau['flag']);

        $atMoe = $this->scanner->identify('NORTH SHEWA', $this->places['moe']);
        $this->assertNull($atMoe['zone']);
        $this->assertSame(ManualScan::FLAG_AMBIGUOUS, $atMoe['flag']);
    }

    public function test_sorting_flags_packages_and_books_that_do_not_belong_to_the_warehouse(): void
    {
        $regionUser = $this->regionUser();
        $sort = fn (string $payload, ?ManualBook $title = null) => $this->scanner->record($regionUser, $this->places['bureau'], $payload, ManualScan::CONTEXT_SORT, ['title' => $title]);

        $ok = $sort('JIMMA', $this->math);
        $this->assertSame([$this->places['jimma']->id, $this->math->id, null, null], [$ok->zone_id, $ok->manual_book_id, $ok->flag, $ok->alert_status]);

        $misrouted = $this->scanner->record($regionUser, $this->places['zoneOffice'], 'JIMMA', ManualScan::CONTEXT_SORT, ['title' => $this->math]);
        $this->assertSame([ManualScan::FLAG_MISROUTE, ManualScan::ALERT_OPEN], [$misrouted->flag, $misrouted->alert_status]);

        $wrongTitle = $sort('EAST SHEWA', $this->amharaMath);
        $this->assertSame([ManualScan::FLAG_WRONG_REGION, ManualScan::ALERT_OPEN], [$wrongTitle->flag, $wrongTitle->alert_status]);

        // Unknown codes are shown to the scanner but are not alerts
        $this->assertNull($sort('SMUDGED LABEL')->alert_status);

        // The national warehouse sorts for every zone
        $this->assertNull($this->scanner->record($this->national, $this->places['moe'], 'JIMMA', ManualScan::CONTEXT_SORT)->flag);
    }

    public function test_receipt_scans_check_the_destination_zone_and_fill_the_receipt(): void
    {
        $shipment = $this->shipment(['full_packages' => 3, 'partial_package_books' => 20, 'partial_teacher_guides' => 1]);
        $zoneUser = $this->zoneUser();

        $component = Livewire::actingAs($zoneUser)
            ->test(ShowShipment::class, ['shipment' => $shipment->id])
            ->assertSet('scanTitleId', (string) $this->math->id)
            ->call('scan', 'EAST SHEWA', 'f8a1e2c4-0000-4000-8000-000000000001')
            ->call('scan', 'EAST SHEWA', 'f8a1e2c4-0000-4000-8000-000000000001')
            ->call('scan', 'East Shewa Zone')
            ->call('scan', 'EAST SHEWA')
            ->call('scan', 'JIMMA')
            ->assertSee('Package for Jimma Zone is at East Shewa Zone Education Department.');

        // The retried scan is recorded once; the misrouted package still physically arrived
        $this->assertSame([$this->math->id => 4], $this->scanner->packageCounts($shipment));
        $this->assertSame(1, ManualScan::query()->where('alert_status', ManualScan::ALERT_OPEN)->count());

        $line = $shipment->lines->first();
        $component->call('applyScans')
            ->assertSet("counts.{$line->id}.full_packages", 3)
            ->assertSet("counts.{$line->id}.partial_package_books", 20)
            ->call('receive');

        $this->assertSame(ManualDistribution::STATUS_RECEIVED, $shipment->fresh()->status);

        $this->expectException(ManualTrackingException::class);
        $this->expectExceptionMessage('already been received');
        $this->scanner->record($zoneUser, $this->places['zoneOffice'], 'EAST SHEWA', ManualScan::CONTEXT_RECEIVE, ['shipment' => $shipment->fresh()]);
    }

    public function test_fewer_scans_than_packages_sent_are_counted_as_full_packages(): void
    {
        $shipment = $this->shipment(['full_packages' => 3, 'partial_package_books' => 20, 'partial_teacher_guides' => 1]);
        $line = $shipment->lines->first();

        Livewire::actingAs($this->zoneUser())
            ->test(ShowShipment::class, ['shipment' => $shipment->id])
            ->call('scan', 'EAST SHEWA')
            ->call('scan', 'EAST SHEWA')
            ->call('applyScans')
            ->assertSet("counts.{$line->id}.full_packages", 2)
            ->assertSet("counts.{$line->id}.partial_package_books", 0);
    }

    public function test_the_dispatcher_cannot_scan_their_own_shipment_in(): void
    {
        $shipment = $this->shipment(['full_packages' => 1]);

        $this->expectException(ManualTrackingException::class);
        $this->expectExceptionMessage('cannot receive it');
        $this->scanner->record($this->national, $this->places['zoneOffice'], 'EAST SHEWA', ManualScan::CONTEXT_RECEIVE, ['shipment' => $shipment]);
    }

    public function test_misroute_alerts_are_closed_by_someone_else_in_the_area(): void
    {
        $zoneUser = $this->zoneUser();
        $alert = $this->scanner->record($zoneUser, $this->places['zoneOffice'], 'JIMMA', ManualScan::CONTEXT_SORT);

        try {
            $this->scanner->resolve($zoneUser, $alert, 'Sent on');
            $this->fail('The scanner closed their own alert');
        } catch (ManualTrackingException $exception) {
            $this->assertStringContainsString('someone other than', $exception->getMessage());
        }

        $outsider = User::factory()->create(['access_level' => User::ACCESS_LEVEL_REGION, 'region_id' => $this->places['amhara']->id]);
        $this->assertSame(0, ManualScan::query()->accessibleBy($outsider)->count());

        Livewire::actingAs($this->regionUser())
            ->test(ScanAlertIndex::class)
            ->assertSee('Package for Jimma Zone')
            ->call('resolve', $alert->id)
            ->assertDispatchedBrowserEvent('alert', fn ($name, $data) => $data['type'] === 'error')
            ->set("notes.{$alert->id}", 'Sent on to Jimma Zone with the next truck')
            ->call('resolve', $alert->id);

        $this->assertSame(ManualScan::ALERT_RESOLVED, $alert->fresh()->alert_status);
    }

    public function test_undoing_a_scan_removes_it_from_counts_and_alerts(): void
    {
        $zoneUser = $this->zoneUser();
        $scan = $this->scanner->record($zoneUser, $this->places['zoneOffice'], 'JIMMA', ManualScan::CONTEXT_SORT);

        try {
            $this->scanner->void($this->regionUser(), $scan);
            $this->fail('Someone else undid the scan');
        } catch (ManualTrackingException) {
        }

        $this->scanner->void($zoneUser, $scan);
        $this->assertNotNull($scan->fresh()->voided_at);
        $this->assertNull($scan->fresh()->alert_status);
    }

    public function test_sorting_screen_counts_packages_per_zone_and_title_and_links_to_a_shipment(): void
    {
        Livewire::actingAs($this->regionUser())
            ->test(SortPackages::class)
            ->set('organizationId', $this->places['bureau']->id)
            ->call('scan', 'Oromia Grade 6 Mathematics Student Textbook')
            ->assertSet('titleId', (string) $this->math->id)
            ->call('scan', 'EAST SHEWA')
            ->call('scan', 'EAST SHEWA')
            ->call('scan', 'JIMMA')
            ->assertSee('Package goes to')
            ->assertSee('Jimma Zone')
            ->assertSee(route('manual-tracking.distribution.add', ['from' => $this->places['bureau']->id, 'zone' => $this->places['eastShewa']->id, 'book' => $this->math->id, 'packages' => 2]));

        $this->assertSame(3, ManualScan::query()->where('kind', ManualScan::KIND_PACKAGE)->count());
    }

    public function test_shipment_form_is_prefilled_from_sorting_and_warns_on_misrouted_scans(): void
    {
        $this->actingAs($this->national)->get(route('manual-tracking.distribution.add', [
            'from' => $this->places['bureau']->id, 'zone' => $this->places['eastShewa']->id, 'book' => $this->math->id, 'packages' => 2,
        ]))->assertOk();

        Livewire::withQueryParams(['from' => $this->places['bureau']->id, 'zone' => $this->places['eastShewa']->id, 'book' => $this->math->id, 'packages' => 2])
            ->actingAs($this->national)
            ->test(CreateShipment::class)
            ->assertSet('from_organization_id', $this->places['bureau']->id)
            ->assertSet('destination.zone_id', (string) $this->places['eastShewa']->id)
            ->assertSet('lines.0.full_packages', 2)
            ->assertSee('East Shewa Zone Education Department')
            ->set('to_organization_id', $this->places['zoneOffice']->id)
            ->call('scan', 'EAST SHEWA')
            ->assertSet('lines.0.full_packages', 3)
            ->call('scan', 'JIMMA')
            ->assertSet('lines.0.full_packages', 4)
            ->assertSee('Package for Jimma Zone is at East Shewa Zone Education Department.')
            ->call('scan', 'Amhara Grade 6 Mathematics Student Textbook')
            ->assertSet('activeLine', 1)
            ->assertSet('lines.1.manual_book_id', $this->amharaMath->id);
    }

    public function test_school_scans_a_book_to_issue_it_to_the_student_at_the_desk(): void
    {
        $school = $this->places['school'];
        $director = User::factory()->create(['access_level' => User::ACCESS_LEVEL_ORGANIZATION, 'organization_id' => $school->id]);
        app(ManualStockService::class)->recordOpeningStock($this->national, $school, $this->math, ['student' => 10, 'teacher' => 1]);
        $student = Student::query()->create([
            'organization_id' => $school->id, 'academic_year' => AcademicYear::current(), 'student_no' => 'IB-1',
            'name' => 'Chaltu Tolosa', 'sex' => 'F', 'grade_id' => $this->math->grade_id, 'section' => 'A',
        ]);

        Livewire::actingAs($director)
            ->test(IssueBooks::class, ['schoolId' => $school->id, 'year' => AcademicYear::current()])
            ->call('scan', '25110-014-1-1709')
            ->assertDispatchedBrowserEvent('alert', fn ($name, $data) => str_contains($data['message'], 'Choose the student'))
            ->call('scanFor', $student->id)
            ->call('scan', 'Oromia Grade 6 Mathematics Teacher guide')
            ->assertDispatchedBrowserEvent('alert', fn ($name, $data) => str_contains($data['message'], 'Teacher Guides tab'))
            ->call('scan', '25110-014-1-1709')
            ->call('scan', '25110-014-1-1709');

        $this->assertSame(1, StudentBookIssue::query()->where('student_id', $student->id)->whereNull('returned_at')->count());
    }

    private function shipment(array $line): ManualDistribution
    {
        app(ManualStockService::class)->recordOpeningStock($this->national, $this->places['bureau'], $this->math, ['student' => 1000, 'teacher' => 30]);

        return app(ManualShipmentService::class)->dispatch($this->national, [
            'from_organization_id' => $this->places['bureau']->id,
            'to_organization_id' => $this->places['zoneOffice']->id,
            'lines' => [['manual_book_id' => $this->math->id] + $line],
        ]);
    }

    private function zoneUser(): User
    {
        return User::factory()->create([
            'access_level' => User::ACCESS_LEVEL_ZONE,
            'region_id' => $this->places['oromia']->id,
            'zone_id' => $this->places['eastShewa']->id,
        ]);
    }

    private function regionUser(): User
    {
        return User::factory()->create([
            'access_level' => User::ACCESS_LEVEL_REGION,
            'region_id' => $this->places['oromia']->id,
        ]);
    }
}
