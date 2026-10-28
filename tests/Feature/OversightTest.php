<?php

namespace Tests\Feature;

use App\Http\Livewire\ManualTracking\Oversight\RecordVisit;
use App\Models\Country;
use App\Models\Grade;
use App\Models\ManualTracking\ManualBook;
use App\Models\ManualTracking\ManualStockBalance;
use App\Models\Organization;
use App\Models\OrganizationType;
use App\Models\Oversight\VerificationVisit;
use App\Models\Region;
use App\Models\School\SchoolEnrolment;
use App\Models\School\Student;
use App\Models\User;
use App\Models\Woreda;
use App\Models\Zone;
use App\Services\ManualTracking\ManualStockService;
use App\Services\ManualTracking\ManualTrackingException;
use App\Services\Oversight\OversightService;
use App\Services\Oversight\VerificationService;
use App\Services\School\SchoolBookService;
use App\Support\AcademicYear;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OversightTest extends TestCase
{
    use RefreshDatabase;

    private Organization $school;
    private Organization $otherRegionSchool;
    private User $director;
    private User $woredaUser;
    private User $national;
    private ManualBook $math;
    private ManualBook $english;
    private int $year;
    private array $students;

    protected function setUp(): void
    {
        parent::setUp();

        $this->year = AcademicYear::current();
        $country = Country::query()->create(['name' => 'Ethiopia', 'code' => 'ET']);
        $oromia = Region::query()->create(['name' => 'Oromia', 'code' => 'OR', 'country_id' => $country->id]);
        $amhara = Region::query()->create(['name' => 'Amhara', 'code' => 'AM', 'country_id' => $country->id]);
        $zone = Zone::query()->create(['name' => 'East Shewa Zone', 'region_id' => $oromia->id, 'country_id' => $country->id]);
        $woreda = Woreda::query()->create(['name' => 'Adama Woreda 01', 'zone_id' => $zone->id]);
        $location = ['country_id' => $country->id, 'region_id' => $oromia->id, 'zone_id' => $zone->id, 'woreda_id' => $woreda->id];
        $schoolType = OrganizationType::query()->create(['name' => 'School']);

        $woredaOffice = Organization::query()->create(['name' => 'Adama Woreda 01 Education Office'] + $location);
        $this->school = Organization::query()->create(['name' => 'Ifa Bula Primary School', 'organization_type_id' => $schoolType->id] + $location);
        $this->otherRegionSchool = Organization::query()->create(['name' => 'Gondar Primary School', 'organization_type_id' => $schoolType->id, 'country_id' => $country->id, 'region_id' => $amhara->id]);

        $this->national = User::factory()->create(['access_level' => User::ACCESS_LEVEL_NATIONAL]);
        $this->director = User::factory()->create(['access_level' => User::ACCESS_LEVEL_ORGANIZATION, 'organization_id' => $this->school->id] + $location);
        $this->woredaUser = User::factory()->create(['access_level' => User::ACCESS_LEVEL_WOREDA, 'organization_id' => $woredaOffice->id] + $location);

        $grade = Grade::query()->create(['name' => '6']);
        $this->math = ManualBook::query()->create(['title' => 'Oromia Grade 6 Mathematics', 'region_id' => $oromia->id, 'grade_id' => $grade->id]);
        $this->english = ManualBook::query()->create(['title' => 'Oromia Grade 6 English', 'region_id' => $oromia->id, 'grade_id' => $grade->id]);

        SchoolEnrolment::query()->create(['organization_id' => $this->school->id, 'academic_year' => $this->year, 'grade_id' => $grade->id, 'male' => 5, 'female' => 5]);
        foreach ([$this->math, $this->english] as $book) {
            app(ManualStockService::class)->recordOpeningStock($this->national, $this->school, $book, ['student' => 8, 'teacher' => 1]);
        }

        $this->students = collect(range(1, 4))->map(fn ($n) => Student::query()->create([
            'organization_id' => $this->school->id, 'academic_year' => $this->year, 'student_no' => "IB-{$n}",
            'name' => "Student {$n}", 'sex' => 'F', 'grade_id' => $grade->id, 'section' => 'A',
        ]))->all();

        // Three students hold both titles; the fourth has nothing yet
        app(SchoolBookService::class)->issueToStudents($this->director, $this->school, [
            $this->students[0]->id => [$this->math->id, $this->english->id],
            $this->students[1]->id => [$this->math->id, $this->english->id],
            $this->students[2]->id => [$this->math->id, $this->english->id],
        ]);
    }

    public function test_batched_summaries_match_the_per_school_calculation(): void
    {
        $allocations = app(\App\Services\School\SchoolAllocationService::class);
        $expected = $allocations->totals($allocations->forSchool($this->school, $this->year));

        $this->assertSame($expected, $allocations->summaries(collect([$this->school]), $this->year)[$this->school->id]);
        $this->assertSame(['required_student' => 20, 'issued_student' => 6, 'on_hand_student' => 10, 'shortfall_student' => 4], array_slice($expected, 0, 4));
    }

    public function test_a_visit_records_blind_counts_and_student_checks_without_touching_stock(): void
    {
        $visit = app(VerificationService::class)->record($this->woredaUser, $this->school, $this->year,
            [$this->math->id => 5, $this->english->id => ''],
            [
                $this->students[0]->id => [$this->math->id => true, $this->english->id => true],
                $this->students[1]->id => [$this->math->id => true],
                $this->students[3]->id => [],
            ],
            'Store room dry.'
        );

        $this->assertSame([1, 5, 1], [$visit->stock_lines, $visit->items()->where('kind', 'stock')->value('expected'), $visit->stock_lines_matching]);
        $this->assertSame([2, 4, 3], [$visit->students_checked, $visit->books_expected, $visit->books_seen]);
        $this->assertSame(0.75, $visit->holdingRate());
        $this->assertSame(5, (int) ManualStockBalance::query()->where('organization_id', $this->school->id)->where('manual_book_id', $this->math->id)->where('edition', 'student')->value('on_hand'));
    }

    public function test_a_school_cannot_verify_itself_and_outsiders_cannot_verify_it(): void
    {
        $verification = app(VerificationService::class);

        foreach ([
            [$this->director, 'cannot verify itself'],
            [User::factory()->create(['access_level' => User::ACCESS_LEVEL_REGION, 'region_id' => $this->otherRegionSchool->region_id]), 'in your area'],
            [$this->woredaUser, 'at least one'],
        ] as [$actor, $message]) {
            try {
                $verification->record($actor, $this->school, $this->year, $actor === $this->woredaUser ? [] : [$this->math->id => 5], []);
                $this->fail("Expected: {$message}");
            } catch (ManualTrackingException $exception) {
                $this->assertStringContainsString($message, $exception->getMessage());
            }
        }

        $this->assertSame(0, VerificationVisit::query()->count());
        $this->actingAs($this->director)->get(route('manual-tracking.visits.create', $this->school->id))->assertForbidden();
    }

    public function test_supervisor_records_a_visit_on_screen(): void
    {
        $component = Livewire::actingAs($this->woredaUser)
            ->test(RecordVisit::class, ['school' => $this->school->id])
            ->assertSee('Oromia Grade 6 English');

        // Only students who hold books are sampled
        $this->assertEqualsCanonicalizing(collect($this->students)->take(3)->pluck('id')->all(), $component->get('sample'));

        $component->set("shelf.{$this->math->id}", 5)
            ->set("shelf.{$this->english->id}", 5)
            ->set("seen.{$this->students[0]->id}.{$this->math->id}", true)
            ->call('save')
            ->assertRedirect();

        $visit = VerificationVisit::query()->sole();
        $this->assertSame([2, 2, 3, 6, 1], [$visit->stock_lines, $visit->stock_lines_matching, $visit->students_checked, $visit->books_expected, $visit->books_seen]);

        $this->actingAs($this->woredaUser)->get(route('manual-tracking.visits.show', $visit->id))->assertOk()->assertSee('✗ Oromia Grade 6 English');
        $this->actingAs(User::factory()->create(['access_level' => User::ACCESS_LEVEL_REGION, 'region_id' => $this->otherRegionSchool->region_id]))
            ->get(route('manual-tracking.visits.show', $visit->id))->assertNotFound();
    }

    public function test_overview_and_exports_cover_only_the_users_area(): void
    {
        $overview = app(OversightService::class)->overview($this->woredaUser, $this->year);
        $this->assertSame([1, 20, 6, 1], [$overview['schools'], $overview['required'], $overview['issued'], $overview['schools_low']]);
        $this->assertSame('East Shewa Zone', $overview['by_zone'][0]['zone']);

        $csv = $this->actingAs($this->woredaUser)->get(route('manual-tracking.exports', 'coverage'))->assertOk()->streamedContent();
        $this->assertStringStartsWith("\xEF\xBB\xBF", $csv);
        $this->assertStringContainsString('"Ifa Bula Primary School",Oromia,"East Shewa Zone","Adama Woreda 01"', $csv);
        $this->assertStringContainsString(',20,6,10,4,30,', $csv);
        $this->assertStringNotContainsString('Gondar', $csv);

        $this->assertStringContainsString('Gondar', $this->actingAs($this->national)->get(route('manual-tracking.exports', 'coverage'))->streamedContent());

        $stock = $this->actingAs($this->woredaUser)->get(route('manual-tracking.exports', 'stock'))->streamedContent();
        $this->assertStringContainsString('"Oromia Grade 6 Mathematics","Student Textbook",5,0', $stock);

        foreach (['shipments', 'visits'] as $type) {
            $this->actingAs($this->woredaUser)->get(route('manual-tracking.exports', $type))->assertOk();
        }
        $this->actingAs($this->woredaUser)->get(route('manual-tracking.exports', 'everything'))->assertNotFound();

        $this->actingAs($this->woredaUser)->get(route('dashboard'))->assertOk()->assertSee('Reaching students')->assertSee('Ifa Bula Primary School');
    }
}
