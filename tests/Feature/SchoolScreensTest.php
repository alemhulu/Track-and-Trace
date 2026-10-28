<?php

namespace Tests\Feature;

use App\Http\Livewire\School\EnrolmentEditor;
use App\Http\Livewire\School\IssueBooks;
use App\Http\Livewire\School\SchoolCoverage;
use App\Http\Livewire\School\StudentRegister;
use App\Http\Livewire\School\TeacherGuides;
use App\Models\Country;
use App\Models\Grade;
use App\Models\ManualTracking\ManualBook;
use App\Models\ManualTracking\ManualStockBalance;
use App\Models\Organization;
use App\Models\Region;
use App\Models\School\SchoolEnrolment;
use App\Models\School\Student;
use App\Models\School\StudentBookIssue;
use App\Models\School\TeacherGuideIssue;
use App\Models\User;
use App\Models\Woreda;
use App\Models\Zone;
use App\Services\ManualTracking\ManualStockService;
use App\Support\AcademicYear;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Tests\TestCase;

class SchoolScreensTest extends TestCase
{
    use RefreshDatabase;

    private Organization $school;
    private User $director;
    private Grade $grade6;
    private ManualBook $math;
    private ManualBook $english;
    private int $year;

    protected function setUp(): void
    {
        parent::setUp();
        $this->grantModuleAccessToNewUsers();

        $this->year = AcademicYear::current();
        $country = Country::query()->create(['name' => 'Ethiopia', 'code' => 'ET']);
        $region = Region::query()->create(['name' => 'Oromia', 'code' => 'OR', 'country_id' => $country->id]);
        $zone = Zone::query()->create(['name' => 'East Shewa Zone', 'region_id' => $region->id, 'country_id' => $country->id]);
        $woreda = Woreda::query()->create(['name' => 'Adama Woreda 01', 'zone_id' => $zone->id]);
        $location = ['country_id' => $country->id, 'region_id' => $region->id, 'zone_id' => $zone->id, 'woreda_id' => $woreda->id];

        $this->school = Organization::query()->create(['name' => 'Ifa Bula Primary School'] + $location);
        $this->director = User::factory()->create(['access_level' => User::ACCESS_LEVEL_ORGANIZATION, 'organization_id' => $this->school->id] + $location);
        $this->grade6 = Grade::query()->create(['name' => '6']);
        Grade::query()->create(['name' => '7']);

        $this->math = ManualBook::query()->create(['title' => 'Oromia Grade 6 Mathematics', 'region_id' => $region->id, 'grade_id' => $this->grade6->id]);
        $this->english = ManualBook::query()->create(['title' => 'Oromia Grade 6 English', 'region_id' => $region->id, 'grade_id' => $this->grade6->id]);

        $national = User::factory()->create(['access_level' => User::ACCESS_LEVEL_NATIONAL]);
        $stock = app(ManualStockService::class);
        $stock->recordOpeningStock($national, $this->school, $this->math, ['student' => 40, 'teacher' => 2]);
        $stock->recordOpeningStock($national, $this->school, $this->english, ['student' => 40, 'teacher' => 2]);
    }

    public function test_director_records_enrolment_per_grade(): void
    {
        Livewire::actingAs($this->director)
            ->test(EnrolmentEditor::class, ['schoolId' => $this->school->id, 'year' => $this->year])
            ->set("rows.{$this->grade6->id}.male", 21)
            ->set("rows.{$this->grade6->id}.female", 19)
            ->call('save')
            ->assertHasNoErrors()
            ->assertEmitted('schoolRecordsChanged');

        $enrolment = SchoolEnrolment::query()->sole();
        $this->assertSame([40, 1], [$enrolment->total(), $enrolment->effectiveSections()]);
    }

    public function test_director_adds_and_imports_students(): void
    {
        $component = Livewire::actingAs($this->director)
            ->test(StudentRegister::class, ['schoolId' => $this->school->id, 'year' => $this->year])
            ->set('form.student_no', 'IB-06-0001')
            ->set('form.name', 'Chaltu Tolosa')
            ->set('form.sex', 'F')
            ->set('form.grade_id', $this->grade6->id)
            ->set('form.section', 'a')
            ->call('saveStudent')
            ->assertHasNoErrors();

        $this->assertSame('A', Student::query()->sole()->section);

        $component
            ->set('csv', UploadedFile::fake()->createWithContent('students.csv', "student_no,name,sex,grade,section\nIB-06-0002,Obsa Gudeta,M,6,A\nIB-06-0003,Ayantu Fayisa,F,Grade 6,B\n"))
            ->call('import')
            ->assertSet('importErrors', [])
            ->assertSee('Obsa Gudeta');

        $this->assertSame(3, Student::query()->count());

        $component
            ->set('csv', UploadedFile::fake()->createWithContent('bad.csv', "student_no,name,sex,grade,section\nIB-06-0004,,M,6,A\n"))
            ->call('import')
            ->assertSet('importErrors', [2 => 'Name is empty.']);
        $this->assertSame(3, Student::query()->count());
    }

    public function test_director_issues_a_class_and_records_a_lost_book(): void
    {
        $students = collect(range(1, 3))->map(fn ($n) => Student::query()->create([
            'organization_id' => $this->school->id, 'academic_year' => $this->year, 'student_no' => "IB-{$n}",
            'name' => "Student {$n}", 'sex' => 'M', 'grade_id' => $this->grade6->id, 'section' => 'A',
        ]));

        $component = Livewire::actingAs($this->director)
            ->test(IssueBooks::class, ['schoolId' => $this->school->id, 'year' => $this->year])
            ->assertSet('gradeId', (string) $this->grade6->id)
            ->assertSee('Oromia Grade 6 English')
            ->call('selectMissing')
            ->call('issue')
            ->assertEmitted('schoolRecordsChanged')
            ->assertSee('✓ Holds');

        $this->assertSame(6, StudentBookIssue::query()->whereNull('returned_at')->count());
        $this->assertSame(37, $this->onHand($this->math));

        $issue = StudentBookIssue::query()->where('student_id', $students[0]->id)->where('manual_book_id', $this->math->id)->sole();
        $component->call('startReturn', $issue->id)
            ->set('returnCondition', 'lost')
            ->call('confirmReturn');

        $this->assertNotNull($issue->fresh()->returned_at);
        $this->assertSame(37, $this->onHand($this->math), 'A lost book does not return to stock');

        Livewire::actingAs($this->director)
            ->test(SchoolCoverage::class, ['schoolId' => $this->school->id, 'year' => $this->year])
            ->assertSee('Oromia Grade 6 Mathematics');
    }

    public function test_director_issues_teacher_guides(): void
    {
        Livewire::actingAs($this->director)
            ->test(TeacherGuides::class, ['schoolId' => $this->school->id, 'year' => $this->year])
            ->set('form.manual_book_id', $this->math->id)
            ->set('form.teacher_name', 'Tigist Alemu')
            ->set('form.section', 'a')
            ->call('issue')
            ->assertHasNoErrors()
            ->assertSee('Tigist Alemu');

        $this->assertSame('A', TeacherGuideIssue::query()->sole()->section);
        $this->assertSame(1, (int) ManualStockBalance::query()->where('manual_book_id', $this->math->id)->where('edition', 'teacher')->value('on_hand'));
    }

    public function test_tabs_cannot_be_opened_for_a_school_outside_the_users_area(): void
    {
        $other = Organization::query()->create(['name' => 'Bilisuma Primary School', 'country_id' => $this->school->country_id, 'region_id' => $this->school->region_id]);
        $otherDirector = User::factory()->create(['access_level' => User::ACCESS_LEVEL_ORGANIZATION, 'organization_id' => $other->id]);

        // Livewire's test harness wraps the not-found error raised while mounting
        $this->expectException(\Illuminate\View\ViewException::class);
        $this->expectExceptionMessage('No query results for model [App\Models\Organization]');
        Livewire::actingAs($otherDirector)->test(EnrolmentEditor::class, ['schoolId' => $this->school->id, 'year' => $this->year]);
    }

    private function onHand(ManualBook $book): int
    {
        return (int) ManualStockBalance::query()
            ->where('organization_id', $this->school->id)
            ->where('manual_book_id', $book->id)
            ->where('edition', 'student')
            ->value('on_hand');
    }
}
