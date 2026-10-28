<?php

namespace App\Services\Oversight;

use App\Models\ManualTracking\ManualBook;
use App\Models\ManualTracking\ManualStockBalance;
use App\Models\Oversight\VerificationVisit;
use App\Models\Oversight\VerificationVisitItem;
use App\Models\Organization;
use App\Models\School\Student;
use App\Models\School\StudentBookIssue;
use App\Models\User;
use App\Services\ManualTracking\ManualStockService;
use App\Services\ManualTracking\ManualTrackingException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Spot checks that the numbers in the system match what is at the school. A visit records
 * findings only; it never changes stock, so differences have to be followed up.
 */
class VerificationService
{
    public function __construct(private ManualStockService $stock)
    {
    }

    /**
     * Titles to count on the shelf: everything the school holds or is expected to hold.
     */
    public function titlesToCount(Organization $school): Collection
    {
        $held = ManualStockBalance::query()->where('organization_id', $school->id)
            ->where('edition', ManualBook::EDITION_STUDENT)->where('on_hand', '>', 0)->pluck('manual_book_id');

        return ManualBook::query()
            ->where(fn ($query) => $query->whereIn('id', $held)->orWhere(fn ($forSchool) => $forSchool
                ->whereNotNull('grade_id')
                ->where('region_id', $school->region_id)))
            ->orderBy('title')
            ->get(['id', 'title']);
    }

    /**
     * Students to check, picked at random by the system so the school cannot choose them.
     */
    public function sampleStudents(Organization $school, int $year, int $size = 10): Collection
    {
        return Student::query()->active()
            ->where('organization_id', $school->id)
            ->where('academic_year', $year)
            ->whereHas('heldBooks')
            ->inRandomOrder()
            ->limit($size)
            ->pluck('id');
    }

    /**
     * @param  array<int, int|string|null>  $shelfCounts  title id => copies counted ('' or null = not counted)
     * @param  array<int, array<int, bool>>  $studentChecks  student id => [title id => shown]
     */
    public function record(User $actor, Organization $school, int $year, array $shelfCounts, array $studentChecks, ?string $notes = null, ?Carbon $visitedAt = null): VerificationVisit
    {
        if (! $this->stock->canActFor($actor, $school)) {
            throw new ManualTrackingException('You can only verify schools in your area.');
        }

        if ((int) $actor->organization_id === (int) $school->id) {
            throw new ManualTrackingException('A school cannot verify itself. The visit must be made by a supervisor.');
        }

        $shelfCounts = array_filter($shelfCounts, fn ($count) => $count !== null && $count !== '');
        foreach ($shelfCounts as $count) {
            if (! is_numeric($count) || (int) $count < 0) {
                throw new ManualTrackingException('Shelf counts must be whole numbers, zero or more.');
            }
        }

        if (! $shelfCounts && ! $studentChecks) {
            throw new ManualTrackingException('Count at least one title or check at least one student.');
        }

        return DB::transaction(function () use ($actor, $school, $year, $shelfCounts, $studentChecks, $notes, $visitedAt): VerificationVisit {
            $visit = VerificationVisit::query()->create([
                'organization_id' => $school->id,
                'academic_year' => $year,
                'visited_by' => $actor->id,
                'visited_at' => $visitedAt ?? now(),
                'notes' => $notes ? trim($notes) : null,
            ]);

            $systemOnHand = ManualStockBalance::query()->where('organization_id', $school->id)
                ->where('edition', ManualBook::EDITION_STUDENT)
                ->whereIn('manual_book_id', array_keys($shelfCounts))
                ->pluck('on_hand', 'manual_book_id');
            $countable = ManualBook::query()->whereKey(array_keys($shelfCounts))->pluck('id')->flip();

            foreach ($shelfCounts as $bookId => $count) {
                if (! isset($countable[$bookId])) {
                    throw new ManualTrackingException('A counted title no longer exists.');
                }

                $visit->items()->create([
                    'kind' => VerificationVisitItem::KIND_STOCK,
                    'manual_book_id' => $bookId,
                    'expected' => (int) ($systemOnHand[$bookId] ?? 0),
                    'found' => (int) $count,
                ]);
            }

            // Only books the system says the student holds are checked
            $held = StudentBookIssue::query()
                ->whereNull('returned_at')
                ->whereIn('student_id', array_keys($studentChecks))
                ->whereHas('student', fn ($student) => $student->where('organization_id', $school->id))
                ->get(['student_id', 'manual_book_id']);

            foreach ($held as $issue) {
                $visit->items()->create([
                    'kind' => VerificationVisitItem::KIND_STUDENT,
                    'manual_book_id' => $issue->manual_book_id,
                    'student_id' => $issue->student_id,
                    'expected' => 1,
                    'found' => empty($studentChecks[$issue->student_id][$issue->manual_book_id]) ? 0 : 1,
                ]);
            }

            $items = $visit->items()->get();
            if ($items->isEmpty()) {
                throw new ManualTrackingException('None of the checked students hold books in the system. Count titles on the shelf instead.');
            }
            $stock = $items->where('kind', VerificationVisitItem::KIND_STOCK);
            $students = $items->where('kind', VerificationVisitItem::KIND_STUDENT);

            $visit->update([
                'stock_lines' => $stock->count(),
                'stock_lines_matching' => $stock->filter->matches()->count(),
                'students_checked' => $students->pluck('student_id')->unique()->count(),
                'books_expected' => $students->count(),
                'books_seen' => $students->sum('found'),
            ]);

            return $visit;
        });
    }
}
