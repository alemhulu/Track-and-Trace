<?php

namespace Tests\Feature;

use App\Models\Country;
use App\Models\Grade;
use App\Models\ManualTracking\ManualBook;
use App\Models\ManualTracking\ManualStockBalance;
use App\Models\Organization;
use App\Models\Region;
use App\Models\School\SchoolEnrolment;
use App\Models\School\Student;
use App\Models\School\StudentBookIssue;
use App\Models\User;
use App\Models\Woreda;
use App\Models\Zone;
use App\Services\ManualTracking\ManualStockService;
use App\Services\ManualTracking\ManualTrackingException;
use App\Services\School\SchoolAllocationService;
use App\Services\School\SchoolBookService;
use App\Services\School\StudentImportService;
use App\Support\AcademicYear;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class SchoolBookWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private SchoolBookService $books;
    private Organization $school;
    private Organization $otherSchool;
    private User $director;
    private ManualBook $grade6Math;
    private ManualBook $amharaMath;
    private Grade $grade6;
    private int $year;

    protected function setUp(): void
    {
        parent::setUp();

        $this->books = app(SchoolBookService::class);
        $this->year = AcademicYear::current();

        $country = Country::query()->create(['name' => 'Ethiopia', 'code' => 'ET']);
        $oromia = Region::query()->create(['name' => 'Oromia', 'code' => 'OR', 'country_id' => $country->id]);
        $amhara = Region::query()->create(['name' => 'Amhara', 'code' => 'AM', 'country_id' => $country->id]);
        $zone = Zone::query()->create(['name' => 'East Shewa Zone', 'region_id' => $oromia->id, 'country_id' => $country->id]);
        $woreda = Woreda::query()->create(['name' => 'Adama Woreda 01', 'zone_id' => $zone->id]);
        $location = ['country_id' => $country->id, 'region_id' => $oromia->id, 'zone_id' => $zone->id, 'woreda_id' => $woreda->id];

        $this->school = Organization::query()->create(['name' => 'Ifa Bula Primary School'] + $location);
        $this->otherSchool = Organization::query()->create(['name' => 'Bilisuma Primary School'] + $location);
        $this->director = User::factory()->create(['access_level' => User::ACCESS_LEVEL_ORGANIZATION, 'organization_id' => $this->school->id] + $location);

        $this->grade6 = Grade::query()->create(['name' => '6']);
        $this->grade6Math = ManualBook::query()->create(['title' => 'Oromia Grade 6 Mathematics', 'region_id' => $oromia->id, 'grade_id' => $this->grade6->id]);
        $this->amharaMath = ManualBook::query()->create(['title' => 'Amhara Grade 6 Mathematics', 'region_id' => $amhara->id, 'grade_id' => $this->grade6->id]);

        $national = User::factory()->create(['access_level' => User::ACCESS_LEVEL_NATIONAL]);
        app(ManualStockService::class)->recordOpeningStock($national, $this->school, $this->grade6Math, ['student' => 50, 'teacher' => 2]);
        app(ManualStockService::class)->recordOpeningStock($national, $this->school, $this->amharaMath, ['student' => 10]);
    }

    public function test_academic_year_follows_the_ethiopian_calendar(): void
    {
        $this->assertSame(2018, AcademicYear::current(Carbon::parse('2026-09-10')));
        $this->assertSame(2019, AcademicYear::current(Carbon::parse('2026-09-11')));
        $this->assertSame('2019 E.C. (2026/27)', AcademicYear::label(2019));
    }

    public function test_issuing_books_takes_copies_from_school_stock_once_per_student(): void
    {
        $students = $this->makeStudents(3);
        $assignments = $students->mapWithKeys(fn ($student) => [$student->id => [$this->grade6Math->id]])->all();

        $this->assertSame(3, $this->books->issueToStudents($this->director, $this->school, $assignments));
        $this->assertSame(0, $this->books->issueToStudents($this->director, $this->school, $assignments), 'Issuing again is a no-op');

        $this->assertSame(47, $this->onHand($this->grade6Math, 'student'));
        $this->assertSame(3, StudentBookIssue::query()->whereNull('returned_at')->count());
    }

    public function test_cannot_issue_more_copies_than_the_school_holds(): void
    {
        $students = $this->makeStudents(51);

        $this->assertRejected(fn () => $this->books->issueToStudents(
            $this->director,
            $this->school,
            $students->mapWithKeys(fn ($student) => [$student->id => [$this->grade6Math->id]])->all()
        ), 'holds only 50');

        $this->assertSame(50, $this->onHand($this->grade6Math, 'student'));
        $this->assertSame(0, StudentBookIssue::query()->count(), 'Nothing is issued when the batch fails');
    }

    public function test_books_printed_for_another_region_are_rejected(): void
    {
        $student = $this->makeStudents(1)->first();

        $this->assertRejected(fn () => $this->books->issueToStudents(
            $this->director,
            $this->school,
            [$student->id => [$this->amharaMath->id]]
        ), 'printed for another region');

        $this->assertSame(10, $this->onHand($this->amharaMath, 'student'));
    }

    public function test_wrong_grade_and_foreign_students_are_rejected(): void
    {
        $grade8 = Grade::query()->create(['name' => '8']);
        $grade8Student = Student::query()->create([
            'organization_id' => $this->school->id, 'academic_year' => $this->year, 'student_no' => 'G8-1',
            'name' => 'Lensa Dinka', 'sex' => 'F', 'grade_id' => $grade8->id,
        ]);
        $this->assertRejected(fn () => $this->books->issueToStudents($this->director, $this->school, [$grade8Student->id => [$this->grade6Math->id]]), 'is not for Grade 8');

        $foreign = Student::query()->create([
            'organization_id' => $this->otherSchool->id, 'academic_year' => $this->year, 'student_no' => 'X-1',
            'name' => 'Obsa Tolosa', 'sex' => 'M', 'grade_id' => $this->grade6->id,
        ]);
        $this->assertRejected(fn () => $this->books->issueToStudents($this->director, $this->school, [$foreign->id => [$this->grade6Math->id]]), 'not registered at this school');
        $this->assertRejected(fn () => $this->books->issueToStudents($this->director, $this->otherSchool, [$foreign->id => [$this->grade6Math->id]]), 'only manage schools in your area');
    }

    public function test_returns_restock_only_usable_copies(): void
    {
        $students = $this->makeStudents(2);
        $this->books->issueToStudents($this->director, $this->school, $students->mapWithKeys(fn ($s) => [$s->id => [$this->grade6Math->id]])->all());
        [$good, $lost] = StudentBookIssue::query()->orderBy('id')->get();

        $this->books->returnFromStudent($this->director, $good, 'good');
        $this->books->returnFromStudent($this->director, $lost, 'lost');

        $this->assertSame(49, $this->onHand($this->grade6Math, 'student'));
        $this->assertRejected(fn () => $this->books->returnFromStudent($this->director, $good, 'good'), 'already been returned');
    }

    public function test_teacher_guides_are_issued_from_and_returned_to_stock(): void
    {
        $issue = $this->books->issueTeacherGuides($this->director, $this->school, $this->grade6Math, $this->year, 'Tigist Alemu', 'A', 2);
        $this->assertSame(0, $this->onHand($this->grade6Math, 'teacher'));

        $this->assertRejected(fn () => $this->books->issueTeacherGuides($this->director, $this->school, $this->grade6Math, $this->year, 'Getnet Ayele', 'B'), 'holds only 0');

        $this->books->returnTeacherGuides($this->director, $issue);
        $this->assertSame(2, $this->onHand($this->grade6Math, 'teacher'));
    }

    public function test_allocation_compares_enrolment_with_stock_and_issues(): void
    {
        SchoolEnrolment::query()->create([
            'organization_id' => $this->school->id, 'academic_year' => $this->year, 'grade_id' => $this->grade6->id,
            'male' => 45, 'female' => 40,
        ]);
        $students = $this->makeStudents(20);
        $this->books->issueToStudents($this->director, $this->school, $students->mapWithKeys(fn ($s) => [$s->id => [$this->grade6Math->id]])->all());

        $row = app(SchoolAllocationService::class)->forSchool($this->school, $this->year)->sole();

        $this->assertSame('Oromia Grade 6 Mathematics', $row['book']->title, 'Other regions\' titles are not allocated');
        $this->assertSame(85, $row['required_student']);
        $this->assertSame(3, $row['required_teacher'], '85 students = 3 sections of up to 40');
        $this->assertSame(20, $row['issued_student']);
        $this->assertSame(30, $row['on_hand_student']);
        $this->assertSame(35, $row['shortfall_student']);
        $this->assertSame(1, $row['shortfall_teacher']);
        $this->assertEqualsWithDelta(20 / 85, $row['coverage'], 0.0001);
    }

    public function test_student_csv_import_is_all_or_nothing(): void
    {
        $import = app(StudentImportService::class);

        $bad = $this->csv("student_no,name,sex,grade,section\nS-1,Abebe Kebede,M,Grade 6,a\nS-2,,F,6,A\nS-1,Hana Girma,X,13,B\n");
        $result = $import->import($this->director, $this->school, $this->year, $bad);
        $this->assertSame([3 => 'Name is empty.', 4 => 'Student number S-1 repeats line 2.'], $result['errors']);
        $this->assertSame(0, Student::query()->count());

        $good = $this->csv("Student No,Name,Sex,Grade,Section\nS-1,Abebe Kebede,Male,Grade 6,a\nS-2,Hana Girma,F,6,A\n");
        $this->assertSame(['created' => 2, 'updated' => 0, 'errors' => []], $import->import($this->director, $this->school, $this->year, $good));

        $again = $this->csv("student_no,name,sex,grade,section\nS-2,Hana Girma Tesfaye,F,6,B\n");
        $this->assertSame(['created' => 0, 'updated' => 1, 'errors' => []], $import->import($this->director, $this->school, $this->year, $again));
        $this->assertSame(['A', 'B'], Student::query()->orderBy('student_no')->pluck('section')->all());
    }

    private function makeStudents(int $count)
    {
        return collect(range(1, $count))->map(fn ($n) => Student::query()->create([
            'organization_id' => $this->school->id,
            'academic_year' => $this->year,
            'student_no' => sprintf('S-%03d', $n),
            'name' => "Student {$n}",
            'sex' => $n % 2 ? 'M' : 'F',
            'grade_id' => $this->grade6->id,
            'section' => 'A',
        ]));
    }

    private function onHand(ManualBook $book, string $edition): int
    {
        return (int) ManualStockBalance::query()
            ->where('organization_id', $this->school->id)
            ->where('manual_book_id', $book->id)
            ->where('edition', $edition)
            ->value('on_hand');
    }

    private function csv(string $contents): string
    {
        $path = tempnam(sys_get_temp_dir(), 'students');
        file_put_contents($path, $contents);

        return $path;
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
