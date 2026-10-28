<?php

namespace App\Services\School;

use App\Models\ManualTracking\ManualAudit;
use App\Models\ManualTracking\ManualBook;
use App\Models\Organization;
use App\Models\School\Student;
use App\Models\School\StudentBookIssue;
use App\Models\School\TeacherGuideIssue;
use App\Models\User;
use App\Services\ManualTracking\ManualStockService;
use App\Services\ManualTracking\ManualTrackingException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * The last mile: books leave the school's stock when handed to a student or teacher and
 * come back when returned in usable condition.
 */
class SchoolBookService
{
    public function __construct(private ManualStockService $stock)
    {
    }

    /**
     * Students who already hold a title are skipped, so issuing a class twice is harmless.
     *
     * @param  array<int, array<int, int>>  $assignments  student id => title ids
     * @return int copies issued
     */
    public function issueToStudents(User $actor, Organization $school, array $assignments, string $condition = 'new', ?Carbon $occurredAt = null): int
    {
        $this->authorize($actor, $school);

        if (! array_key_exists($condition, StudentBookIssue::CONDITIONS)) {
            throw new ManualTrackingException('Choose the condition of the books being issued.');
        }

        $assignments = array_filter(array_map(fn ($ids) => array_values(array_unique(array_map('intval', (array) $ids))), $assignments));
        if (empty($assignments)) {
            throw new ManualTrackingException('Select at least one student and title.');
        }

        return DB::transaction(function () use ($actor, $school, $assignments, $condition, $occurredAt): int {
            $students = Student::query()
                ->where('organization_id', $school->id)
                ->whereKey(array_keys($assignments))
                ->with(['heldBooks:id,student_id,manual_book_id', 'grade:id,name'])
                ->get()
                ->keyBy('id');

            if ($students->count() !== count($assignments)) {
                throw new ManualTrackingException('Some selected students are not registered at this school.');
            }

            $books = ManualBook::query()->whereKey(collect($assignments)->flatten()->unique())->get()->keyBy('id');
            $issues = [];

            foreach ($assignments as $studentId => $bookIds) {
                $student = $students[$studentId];

                if ($student->status !== Student::STATUS_ACTIVE) {
                    throw new ManualTrackingException("{$student->name} is not an active student.");
                }

                foreach ($bookIds as $bookId) {
                    $book = $books[$bookId] ?? throw new ManualTrackingException('A selected title no longer exists.');
                    $this->ensureBookFitsStudent($book, $student, $school);

                    if ($student->heldBooks->contains('manual_book_id', $book->id)) {
                        continue;
                    }

                    $issues[] = ['student_id' => $student->id, 'manual_book_id' => $book->id];
                }
            }

            foreach (collect($issues)->countBy('manual_book_id') as $bookId => $count) {
                $this->stock->move($school, $books[$bookId], ManualBook::EDITION_STUDENT, -$count, ManualStockService::MOVEMENT_ISSUE_STUDENT, [
                    'actor' => $actor,
                    'notes' => "Issued to {$count} " . ($count === 1 ? 'student' : 'students'),
                    'occurred_at' => $occurredAt,
                ]);
            }

            $now = now();
            foreach (array_chunk($issues, 500) as $chunk) {
                StudentBookIssue::query()->insert(array_map(fn ($issue) => $issue + [
                    'issued_at' => $occurredAt ?? $now,
                    'issued_by' => $actor->id,
                    'condition' => $condition,
                    'created_at' => $now,
                    'updated_at' => $now,
                ], $chunk));
            }

            if ($issues) {
                $this->audit($actor, 'books_issued_to_students', $school, [
                    'copies' => count($issues),
                    'students' => collect($issues)->pluck('student_id')->unique()->count(),
                ]);
            }

            return count($issues);
        });
    }

    public function returnFromStudent(User $actor, StudentBookIssue $issue, string $condition, ?Carbon $occurredAt = null): void
    {
        $usable = array_key_exists($condition, StudentBookIssue::CONDITIONS);
        if (! $usable && ! array_key_exists($condition, StudentBookIssue::UNUSABLE_RETURN_CONDITIONS)) {
            throw new ManualTrackingException('Choose the condition of the returned book.');
        }

        DB::transaction(function () use ($actor, $issue, $condition, $usable, $occurredAt): void {
            $issue = StudentBookIssue::query()->with(['student.school', 'book'])->lockForUpdate()->findOrFail($issue->id);
            $school = $issue->student->school;
            $this->authorize($actor, $school);

            if ($issue->returned_at) {
                throw new ManualTrackingException('This book has already been returned.');
            }

            // Damaged or lost copies left stock when issued and stay out of it
            if ($usable) {
                $this->stock->move($school, $issue->book, ManualBook::EDITION_STUDENT, 1, ManualStockService::MOVEMENT_STUDENT_RETURN, [
                    'actor' => $actor,
                    'notes' => "Returned by {$issue->student->name} ({$condition})",
                    'occurred_at' => $occurredAt,
                ]);
            }

            $issue->update(['returned_at' => $occurredAt ?? now(), 'returned_by' => $actor->id, 'return_condition' => $condition]);
        });
    }

    public function issueTeacherGuides(User $actor, Organization $school, ManualBook $book, int $academicYear, string $teacherName, ?string $section, int $quantity = 1): TeacherGuideIssue
    {
        $this->authorize($actor, $school);

        if (! $book->has_teacher_guide) {
            throw new ManualTrackingException("{$book->title} has no teacher guide.");
        }

        if (trim($teacherName) === '' || $quantity < 1) {
            throw new ManualTrackingException('Enter the teacher and at least one guide.');
        }

        return DB::transaction(function () use ($actor, $school, $book, $academicYear, $teacherName, $section, $quantity): TeacherGuideIssue {
            $this->stock->move($school, $book, ManualBook::EDITION_TEACHER, -$quantity, ManualStockService::MOVEMENT_ISSUE_TEACHER, [
                'actor' => $actor,
                'notes' => "Issued to {$teacherName}",
            ]);

            return TeacherGuideIssue::query()->create([
                'organization_id' => $school->id,
                'academic_year' => $academicYear,
                'manual_book_id' => $book->id,
                'teacher_name' => trim($teacherName),
                'section' => $section ?: null,
                'quantity' => $quantity,
                'issued_at' => now(),
                'issued_by' => $actor->id,
            ]);
        });
    }

    public function returnTeacherGuides(User $actor, TeacherGuideIssue $issue): void
    {
        DB::transaction(function () use ($actor, $issue): void {
            $issue = TeacherGuideIssue::query()->with(['school', 'book'])->lockForUpdate()->findOrFail($issue->id);
            $this->authorize($actor, $issue->school);

            if ($issue->returned_at) {
                throw new ManualTrackingException('These guides have already been returned.');
            }

            $this->stock->move($issue->school, $issue->book, ManualBook::EDITION_TEACHER, $issue->quantity, ManualStockService::MOVEMENT_TEACHER_RETURN, [
                'actor' => $actor,
                'notes' => "Returned by {$issue->teacher_name}",
            ]);

            $issue->update(['returned_at' => now(), 'returned_by' => $actor->id]);
        });
    }

    public function authorize(User $actor, Organization $school): void
    {
        if (! $this->stock->canActFor($actor, $school)) {
            throw new ManualTrackingException('You can only manage schools in your area.');
        }
    }

    // A Grade 6 Oromia student gets Oromia Grade 6 books, nothing else
    private function ensureBookFitsStudent(ManualBook $book, Student $student, Organization $school): void
    {
        if ($book->grade_id && (int) $book->grade_id !== (int) $student->grade_id) {
            throw new ManualTrackingException("{$book->title} is not for Grade {$student->grade->name} ({$student->name}).");
        }

        if ($book->region_id && $school->region_id && (int) $book->region_id !== (int) $school->region_id) {
            throw new ManualTrackingException("{$book->title} is printed for another region than {$school->name}.");
        }
    }

    private function audit(User $actor, string $action, Organization $school, array $meta): void
    {
        ManualAudit::query()->create([
            'user_id' => $actor->id,
            'action' => $action,
            'auditable_type' => Organization::class,
            'auditable_id' => $school->id,
            'meta' => $meta,
        ]);
    }
}
