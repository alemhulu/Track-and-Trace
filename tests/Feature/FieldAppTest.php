<?php

namespace Tests\Feature;

use App\Models\Country;
use App\Models\Grade;
use App\Models\ManualTracking\FieldSyncAction;
use App\Models\ManualTracking\ManualBook;
use App\Models\ManualTracking\ManualBookCode;
use App\Models\ManualTracking\ManualDistribution;
use App\Models\ManualTracking\ManualStockBalance;
use App\Models\ManualTracking\ManualStockLedger;
use App\Models\Organization;
use App\Models\OrganizationType;
use App\Models\Region;
use App\Models\School\Student;
use App\Models\School\StudentBookIssue;
use App\Models\User;
use App\Models\Woreda;
use App\Models\Zone;
use App\Services\ManualTracking\ManualShipmentService;
use App\Services\ManualTracking\ManualStockService;
use App\Support\AcademicYear;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class FieldAppTest extends TestCase
{
    use RefreshDatabase;

    private Organization $woredaOffice;
    private Organization $school;
    private User $national;
    private User $director;
    private ManualBook $math;
    private Student $student;

    protected function setUp(): void
    {
        parent::setUp();
        $this->grantModuleAccessToNewUsers();

        $country = Country::query()->create(['name' => 'Ethiopia', 'code' => 'ET']);
        $region = Region::query()->create(['name' => 'Oromia', 'code' => 'OR', 'country_id' => $country->id]);
        $zone = Zone::query()->create(['name' => 'East Shewa Zone', 'region_id' => $region->id, 'country_id' => $country->id]);
        $woreda = Woreda::query()->create(['name' => 'Adama Woreda 01', 'zone_id' => $zone->id]);
        $location = ['country_id' => $country->id, 'region_id' => $region->id, 'zone_id' => $zone->id, 'woreda_id' => $woreda->id];
        $schoolType = OrganizationType::query()->create(['name' => 'School']);

        $this->woredaOffice = Organization::query()->create(['name' => 'Adama Woreda 01 Education Office'] + $location);
        $this->school = Organization::query()->create(['name' => 'Ifa Bula Primary School', 'organization_type_id' => $schoolType->id] + $location);

        $this->national = User::factory()->create(['access_level' => User::ACCESS_LEVEL_NATIONAL]);
        $this->director = User::factory()->create(['access_level' => User::ACCESS_LEVEL_ORGANIZATION, 'organization_id' => $this->school->id] + $location);

        $grade = Grade::query()->create(['name' => '6']);
        $this->math = ManualBook::query()->create(['title' => 'Oromia Grade 6 Mathematics', 'region_id' => $region->id, 'grade_id' => $grade->id]);
        ManualBookCode::query()->create(['manual_book_id' => $this->math->id, 'edition' => 'student', 'payload' => 'Oromia Grade 6 Mathematics Student Textbook', 'print_code' => '25110-014-1-1709']);

        $this->student = Student::query()->create([
            'organization_id' => $this->school->id, 'academic_year' => AcademicYear::current(), 'student_no' => 'IB-1',
            'name' => 'Chaltu Tolosa', 'sex' => 'F', 'grade_id' => $grade->id, 'section' => 'A',
        ]);

        app(ManualStockService::class)->recordOpeningStock($this->national, $this->woredaOffice, $this->math, ['student' => 400, 'teacher' => 10]);
    }

    public function test_pack_holds_what_a_school_needs_offline_and_only_for_its_own_school(): void
    {
        $shipment = $this->shipToSchool(80);

        $this->actingAs($this->director)->get(route('field.app'))->assertOk()->assertSee('Download for offline use');

        $this->actingAs($this->director)->getJson(route('field.pack', ['organization' => $this->school->id]))
            ->assertOk()
            ->assertJsonPath('organization.is_school', true)
            ->assertJsonPath('organization.zone_key', 'east shewa')
            ->assertJsonPath('shipments.0.reference', $shipment->reference)
            ->assertJsonPath('shipments.0.lines.0.student_books', 80)
            ->assertJsonPath('shipments.0.lines.0.packages', 2)
            ->assertJsonPath('titles.0.codes.0.text', 'oromia grade 6 mathematics student textbook')
            ->assertJsonPath('students.0.name', 'Chaltu Tolosa');

        $this->actingAs($this->director)->getJson(route('field.pack', ['organization' => $this->woredaOffice->id]))->assertNotFound();
    }

    public function test_offline_receipt_and_issues_sync_once_and_keep_the_time_they_happened(): void
    {
        $shipment = $this->travelTo(now()->subDay(), fn () => $this->shipToSchool(80));
        $line = $shipment->lines->first();
        $receivedAt = now()->subHours(5)->startOfMinute();
        $issuedAt = now()->subHours(2)->startOfMinute();

        $actions = [
            $this->action('receive_shipment', ['shipment_id' => $shipment->id, 'counts' => [$line->id => ['student_books' => 78, 'teacher_guides' => 2, 'damaged_quantity' => 1]], 'notes' => 'Box wet'], $receivedAt),
            $this->action('issue_books', ['student_id' => $this->student->id, 'title_ids' => [$this->math->id], 'condition' => 'new'], $issuedAt),
        ];

        $response = $this->actingAs($this->director)->postJson(route('field.sync'), ['actions' => $actions])->assertOk();
        $this->assertSame(['applied', 'applied'], array_column($response->json('results'), 'status'));

        // The phone lost the reply and sends the same queue again
        $again = $this->actingAs($this->director)->postJson(route('field.sync'), ['actions' => $actions])->assertOk();
        $this->assertSame(['applied', 'applied'], array_column($again->json('results'), 'status'));

        $shipment->refresh();
        $this->assertSame(ManualDistribution::STATUS_RECEIVED_WITH_DISCREPANCY, $shipment->status);
        $this->assertSame($receivedAt->toDateTimeString(), $shipment->received_at->toDateTimeString());
        $this->assertSame('Box wet', $shipment->receipt_notes);
        $this->assertSame(76, $this->onHand($this->school));

        $issue = StudentBookIssue::query()->sole();
        $this->assertSame($issuedAt->toDateTimeString(), $issue->issued_at->toDateTimeString());
        $this->assertSame(1, ManualStockLedger::query()->where('organization_id', $this->school->id)->where('movement_qty', -1)->count());
        $this->assertSame(2, FieldSyncAction::query()->count());

        // A phone clock running behind cannot date a receipt before the truck left
        $late = $this->shipToSchool(40);
        $this->actingAs($this->director)->postJson(route('field.sync'), ['actions' => [
            $this->action('receive_shipment', ['shipment_id' => $late->id, 'counts' => [$late->lines->first()->id => ['student_books' => 40, 'teacher_guides' => 1]]], now()->subHours(3)),
        ]])->assertJsonPath('results.0.status', 'applied');
        $this->assertTrue($late->fresh()->received_at->gte($late->distributed_at));
    }

    public function test_actions_that_break_a_rule_are_rejected_with_a_reason_and_change_nothing(): void
    {
        $results = $this->actingAs($this->director)->postJson(route('field.sync'), ['actions' => [
            // The school holds no stock yet
            $this->action('issue_books', ['student_id' => $this->student->id, 'title_ids' => [$this->math->id]]),
            $this->action('return_book', ['student_id' => $this->student->id, 'title_id' => $this->math->id]),
            $this->action('issue_books', ['student_id' => $this->student->id, 'title_ids' => [$this->math->id]], now()->subDays(90)),
            ['uuid' => 'not-a-uuid', 'type' => 'issue_books'],
        ]])->assertOk()->json('results');

        $this->assertSame(['rejected', 'rejected', 'rejected', 'rejected'], array_column($results, 'status'));
        $this->assertStringContainsString('holds only 0', $results[0]['message']);
        $this->assertStringContainsString('does not hold', $results[1]['message']);
        $this->assertStringContainsString('days old', $results[2]['message']);
        $this->assertSame(0, StudentBookIssue::query()->count());
    }

    public function test_another_school_cannot_sync_work_for_this_school(): void
    {
        $other = Organization::query()->create(['name' => 'Bilisuma Primary School', 'country_id' => $this->school->country_id, 'region_id' => $this->school->region_id]);
        $otherDirector = User::factory()->create(['access_level' => User::ACCESS_LEVEL_ORGANIZATION, 'organization_id' => $other->id]);
        $this->shipToSchool(40);
        app(ManualStockService::class)->recordOpeningStock($this->national, $this->school, $this->math, ['student' => 5, 'teacher' => 0]);

        $result = $this->actingAs($otherDirector)->postJson(route('field.sync'), ['actions' => [
            $this->action('issue_books', ['student_id' => $this->student->id, 'title_ids' => [$this->math->id]]),
        ]])->json('results.0');

        $this->assertSame('rejected', $result['status']);
        $this->assertSame(0, StudentBookIssue::query()->count());
    }

    public function test_offline_actions_need_the_same_permission_as_online(): void
    {
        $this->shipToSchool(40);
        app(ManualStockService::class)->recordOpeningStock($this->national, $this->school, $this->math, ['student' => 5, 'teacher' => 0]);
        $this->director->revokePermissionTo('manual-tracking.schools.issue');

        $result = $this->actingAs($this->director->fresh())->postJson(route('field.sync'), ['actions' => [
            $this->action('issue_books', ['student_id' => $this->student->id, 'title_ids' => [$this->math->id]]),
        ]])->json('results.0');

        $this->assertSame('rejected', $result['status']);
        $this->assertStringContainsString('not allowed', $result['message']);
        $this->assertSame(0, StudentBookIssue::query()->count());
    }

    public function test_paper_forms_print_for_people_in_the_area(): void
    {
        $shipment = $this->shipToSchool(85);

        $this->actingAs($this->director)->get(route('manual-tracking.shipments.print', $shipment->id))
            ->assertOk()
            ->assertSee($shipment->reference)
            ->assertSee('2 + 1 partial')
            ->assertSee('Received by (name, signature, stamp)');

        $this->actingAs($this->director)->get(route('manual-tracking.schools.print', ['school' => $this->school->id, 'grade' => $this->student->grade_id]))
            ->assertOk()
            ->assertSee('Chaltu Tolosa')
            ->assertSee('Mathematics');

        $outsider = User::factory()->create(['access_level' => User::ACCESS_LEVEL_ORGANIZATION, 'organization_id' => $this->woredaOffice->id]);
        $this->actingAs($outsider)->get(route('manual-tracking.schools.print', ['school' => $this->school->id, 'grade' => $this->student->grade_id]))->assertNotFound();
    }

    private function shipToSchool(int $studentBooks): ManualDistribution
    {
        return app(ManualShipmentService::class)->dispatch($this->national, [
            'from_organization_id' => $this->woredaOffice->id,
            'to_organization_id' => $this->school->id,
            'lines' => [['manual_book_id' => $this->math->id, 'student_books' => $studentBooks, 'teacher_guides' => intdiv($studentBooks, 40)]],
        ]);
    }

    private function action(string $type, array $data, $recordedAt = null): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'type' => $type,
            'organization_id' => $this->school->id,
            'recorded_at' => ($recordedAt ?? now())->copy()->utc()->toIso8601String(),
            'data' => $data,
        ];
    }

    private function onHand(Organization $holder): int
    {
        return (int) ManualStockBalance::query()->where('organization_id', $holder->id)->where('manual_book_id', $this->math->id)->where('edition', 'student')->value('on_hand');
    }
}
