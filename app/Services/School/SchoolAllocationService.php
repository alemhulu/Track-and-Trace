<?php

namespace App\Services\School;

use App\Models\ManualTracking\ManualBook;
use App\Models\ManualTracking\ManualStockBalance;
use App\Models\Organization;
use App\Models\School\SchoolEnrolment;
use App\Models\School\Student;
use App\Models\School\StudentBookIssue;
use App\Models\School\TeacherGuideIssue;
use Illuminate\Support\Collection;

/**
 * What a school needs (one student book per enrolled student, one teacher guide per
 * section) against what it holds and what it has handed out.
 */
class SchoolAllocationService
{
    /**
     * @return Collection<int, array{
     *     book: ManualBook, grade: string, enrolled: int, registered: int,
     *     required_student: int, required_teacher: int, on_hand_student: int, on_hand_teacher: int,
     *     issued_student: int, issued_teacher: int, shortfall_student: int, shortfall_teacher: int, coverage: ?float
     * }>
     */
    public function forSchool(Organization $school, int $year): Collection
    {
        $enrolments = SchoolEnrolment::query()
            ->where('organization_id', $school->id)
            ->where('academic_year', $year)
            ->get()
            ->keyBy('grade_id');

        $registered = Student::query()
            ->active()
            ->where('organization_id', $school->id)
            ->where('academic_year', $year)
            ->selectRaw('grade_id, COUNT(*) as total')
            ->groupBy('grade_id')
            ->pluck('total', 'grade_id');

        $gradeIds = $enrolments->keys()->merge($registered->keys())->unique();
        if ($gradeIds->isEmpty()) {
            return collect();
        }

        $books = ManualBook::query()
            ->whereIn('grade_id', $gradeIds)
            ->where(fn ($query) => $query->whereNull('region_id')->orWhere('region_id', $school->region_id))
            ->with('grade:id,name')
            ->get()
            ->sortBy(fn ($book) => sprintf('%03d %s', (int) $book->grade->name, $book->title))
            ->values();

        $balances = ManualStockBalance::query()
            ->where('organization_id', $school->id)
            ->whereIn('manual_book_id', $books->pluck('id'))
            ->get()
            ->groupBy('manual_book_id');

        $issuedStudent = StudentBookIssue::query()
            ->whereNull('returned_at')
            ->whereHas('student', fn ($query) => $query->where('organization_id', $school->id)->where('academic_year', $year))
            ->selectRaw('manual_book_id, COUNT(*) as total')
            ->groupBy('manual_book_id')
            ->pluck('total', 'manual_book_id');

        $issuedTeacher = TeacherGuideIssue::query()
            ->where('organization_id', $school->id)
            ->where('academic_year', $year)
            ->whereNull('returned_at')
            ->selectRaw('manual_book_id, SUM(quantity) as total')
            ->groupBy('manual_book_id')
            ->pluck('total', 'manual_book_id');

        return $books->map(function (ManualBook $book) use ($enrolments, $registered, $balances, $issuedStudent, $issuedTeacher) {
            $enrolment = $enrolments[$book->grade_id] ?? null;
            $enrolled = $enrolment?->total() ?? 0;
            $registeredCount = (int) ($registered[$book->grade_id] ?? 0);
            $demand = $enrolled ?: $registeredCount;
            $sections = $enrolment?->effectiveSections() ?? (int) ceil($registeredCount / SchoolEnrolment::STUDENTS_PER_SECTION);
            $onHand = fn (string $edition) => (int) optional(($balances[$book->id] ?? collect())->firstWhere('edition', $edition))->on_hand;

            $row = [
                'book' => $book,
                'grade' => $book->grade->name,
                'enrolled' => $enrolled,
                'registered' => $registeredCount,
                'required_student' => $demand,
                'required_teacher' => $book->has_teacher_guide ? $sections : 0,
                'on_hand_student' => $onHand(ManualBook::EDITION_STUDENT),
                'on_hand_teacher' => $onHand(ManualBook::EDITION_TEACHER),
                'issued_student' => (int) ($issuedStudent[$book->id] ?? 0),
                'issued_teacher' => (int) ($issuedTeacher[$book->id] ?? 0),
            ];

            $row['shortfall_student'] = max(0, $row['required_student'] - $row['on_hand_student'] - $row['issued_student']);
            $row['shortfall_teacher'] = max(0, $row['required_teacher'] - $row['on_hand_teacher'] - $row['issued_teacher']);
            $row['coverage'] = $demand > 0 ? min(1, $row['issued_student'] / $demand) : null;

            return $row;
        });
    }

    /**
     * The same totals as totals(forSchool()) for many schools at once, in a handful of
     * queries per 500 schools, for dashboards and exports.
     *
     * @param  Collection<int, Organization>  $schools
     * @return array<int, array{required_student: int, issued_student: int, on_hand_student: int, shortfall_student: int, coverage: ?float}>
     */
    public function summaries(Collection $schools, int $year): array
    {
        $titles = ManualBook::query()->whereNotNull('grade_id')->get(['id', 'grade_id', 'region_id'])->groupBy('grade_id');
        $summaries = [];

        foreach ($schools->chunk(500) as $chunk) {
            $ids = $chunk->pluck('id');

            $enrolled = SchoolEnrolment::query()->whereIn('organization_id', $ids)->where('academic_year', $year)
                ->get(['organization_id', 'grade_id', 'male', 'female'])
                ->groupBy('organization_id')
                ->map(fn ($rows) => $rows->mapWithKeys(fn ($row) => [$row->grade_id => $row->total()]));

            $registered = Student::query()->active()->whereIn('organization_id', $ids)->where('academic_year', $year)
                ->selectRaw('organization_id, grade_id, COUNT(*) as total')
                ->groupBy('organization_id', 'grade_id')
                ->get()
                ->groupBy('organization_id')
                ->map(fn ($rows) => $rows->pluck('total', 'grade_id'));

            $issued = StudentBookIssue::query()
                ->join('students', 'students.id', '=', 'student_book_issues.student_id')
                ->whereNull('student_book_issues.returned_at')
                ->whereIn('students.organization_id', $ids)
                ->where('students.academic_year', $year)
                ->selectRaw('students.organization_id, student_book_issues.manual_book_id, COUNT(*) as total')
                ->groupBy('students.organization_id', 'student_book_issues.manual_book_id')
                ->get()
                ->groupBy('organization_id')
                ->map(fn ($rows) => $rows->pluck('total', 'manual_book_id'));

            $onHand = ManualStockBalance::query()->whereIn('organization_id', $ids)->where('edition', ManualBook::EDITION_STUDENT)
                ->get(['organization_id', 'manual_book_id', 'on_hand'])
                ->groupBy('organization_id')
                ->map(fn ($rows) => $rows->pluck('on_hand', 'manual_book_id'));

            foreach ($chunk as $school) {
                $totals = ['required_student' => 0, 'issued_student' => 0, 'on_hand_student' => 0, 'shortfall_student' => 0];
                $schoolEnrolled = $enrolled[$school->id] ?? collect();
                $schoolRegistered = $registered[$school->id] ?? collect();

                foreach ($schoolEnrolled->keys()->merge($schoolRegistered->keys())->unique() as $gradeId) {
                    $demand = (int) ($schoolEnrolled[$gradeId] ?? 0) ?: (int) ($schoolRegistered[$gradeId] ?? 0);

                    foreach ($titles[$gradeId] ?? [] as $book) {
                        if ($book->region_id && (int) $book->region_id !== (int) $school->region_id) {
                            continue;
                        }

                        $bookIssued = (int) (($issued[$school->id] ?? collect())[$book->id] ?? 0);
                        $bookOnHand = (int) (($onHand[$school->id] ?? collect())[$book->id] ?? 0);
                        $totals['required_student'] += $demand;
                        $totals['issued_student'] += $bookIssued;
                        $totals['on_hand_student'] += $bookOnHand;
                        $totals['shortfall_student'] += max(0, $demand - $bookOnHand - $bookIssued);
                    }
                }

                $totals['coverage'] = $totals['required_student'] > 0 ? min(1, $totals['issued_student'] / $totals['required_student']) : null;
                $summaries[$school->id] = $totals;
            }
        }

        return $summaries;
    }

    /**
     * @return array{required_student: int, issued_student: int, on_hand_student: int, shortfall_student: int, coverage: ?float}
     */
    public function totals(Collection $rows): array
    {
        $required = (int) $rows->sum('required_student');
        $issued = (int) $rows->sum('issued_student');

        return [
            'required_student' => $required,
            'issued_student' => $issued,
            'on_hand_student' => (int) $rows->sum('on_hand_student'),
            'shortfall_student' => (int) $rows->sum('shortfall_student'),
            'coverage' => $required > 0 ? min(1, $issued / $required) : null,
        ];
    }
}
