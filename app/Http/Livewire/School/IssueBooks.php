<?php

namespace App\Http\Livewire\School;

use App\Http\Livewire\Concerns\AuthorizesPermissions;
use App\Http\Livewire\School\Concerns\LoadsSchool;
use App\Models\Grade;
use App\Models\ManualTracking\ManualBook;
use App\Models\ManualTracking\ManualScan;
use App\Models\ManualTracking\ManualStockBalance;
use App\Models\School\Student;
use App\Models\School\StudentBookIssue;
use App\Services\ManualTracking\ManualScanService;
use App\Services\ManualTracking\ManualTrackingException;
use App\Services\School\SchoolBookService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class IssueBooks extends Component
{
    use LoadsSchool;
    use AuthorizesPermissions;

    public $gradeId = '';
    public $section = '';
    public $condition = 'new';

    // "{studentId}_{bookId}" => true for cells ticked to issue
    public $selected = [];

    public $returningIssueId;
    public $returnCondition = 'good';

    // Student receiving the books being scanned at the desk
    public $scanStudentId;

    public function mount()
    {
        $this->school();
        $this->gradeId = (string) ($this->gradeIds()->first() ?? '');
    }

    public function updatedGradeId()
    {
        $this->reset('section', 'selected', 'returningIssueId', 'scanStudentId');
    }

    public function updatedSection()
    {
        $this->reset('selected', 'returningIssueId', 'scanStudentId');
    }

    public function scanFor($studentId)
    {
        $this->scanStudentId = $this->students()->contains('id', (int) $studentId) ? (int) $studentId : null;
    }

    public function nextStudent()
    {
        $ids = $this->students()->pluck('id')->values();
        $position = $ids->search($this->scanStudentId);
        $this->scanStudentId = $position === false ? $ids->first() : $ids->get($position + 1);
    }

    // Scanning the QR of the book handed over issues that title to the student at the desk
    public function scan($payload, $clientUuid = null)
    {
        if (! $this->scanStudentId) {
            return $this->scanFeedback(false, 'Choose the student receiving the books first.');
        }

        $result = app(ManualScanService::class)->identify((string) $payload, $this->school());

        if ($result['kind'] !== ManualScan::KIND_BOOK) {
            return $this->scanFeedback(false, $result['kind'] === ManualScan::KIND_PACKAGE
                ? 'That is a package label. Scan the book itself.'
                : $result['message']);
        }

        if ($result['edition'] !== ManualBook::EDITION_STUDENT) {
            return $this->scanFeedback(false, 'That is a teacher guide. Issue it from the Teacher Guides tab.');
        }

        try {
            $count = app(SchoolBookService::class)->issueToStudents(Auth::user(), $this->school(), [$this->scanStudentId => [$result['book']->id]], $this->condition);
        } catch (ManualTrackingException $exception) {
            return $this->scanFeedback(false, $exception->getMessage());
        }

        $this->emit('schoolRecordsChanged');
        $this->scanFeedback(true);
        $this->alert($count ? 'success' : 'info', $count ? "Issued {$result['book']->title}." : 'This student already holds that title.');
    }

    public function render()
    {
        $school = $this->school();
        $students = $this->students();
        $titles = $this->titles($school);

        $held = StudentBookIssue::query()
            ->whereNull('returned_at')
            ->whereIn('student_id', $students->pluck('id'))
            ->get(['id', 'student_id', 'manual_book_id'])
            ->mapWithKeys(fn ($issue) => [$issue->student_id . '_' . $issue->manual_book_id => $issue->id]);

        return view('livewire.school.issue-books', [
            'grades' => Grade::query()->whereKey($this->gradeIds())->orderByRaw('CAST(name AS UNSIGNED)')->get(['id', 'name']),
            'sections' => Student::query()->active()
                ->where('organization_id', $this->schoolId)->where('academic_year', $this->year)
                ->where('grade_id', $this->gradeId ?: 0)
                ->whereNotNull('section')->distinct()->orderBy('section')->pluck('section'),
            'students' => $students,
            'titles' => $titles,
            'held' => $held,
            'onHand' => ManualStockBalance::query()
                ->where('organization_id', $school->id)
                ->where('edition', ManualBook::EDITION_STUDENT)
                ->whereIn('manual_book_id', $titles->pluck('id'))
                ->pluck('on_hand', 'manual_book_id'),
            'scanStudent' => $this->scanStudentId ? $students->firstWhere('id', $this->scanStudentId) : null,
            'returning' => $this->returningIssueId
                ? StudentBookIssue::query()->with(['student:id,name', 'book:id,title'])->find($this->returningIssueId)
                : null,
        ]);
    }

    public function selectMissing()
    {
        $students = $this->students();
        $held = StudentBookIssue::query()->whereNull('returned_at')->whereIn('student_id', $students->pluck('id'))
            ->get(['student_id', 'manual_book_id'])
            ->map(fn ($issue) => $issue->student_id . '_' . $issue->manual_book_id)
            ->flip();

        foreach ($students as $student) {
            foreach ($this->titles($this->school()) as $title) {
                $key = $student->id . '_' . $title->id;
                if (! isset($held[$key])) {
                    $this->selected[$key] = true;
                }
            }
        }
    }

    public function clearSelection()
    {
        $this->selected = [];
    }

    public function issue(SchoolBookService $books)
    {
        $assignments = [];
        foreach (array_keys(array_filter($this->selected)) as $key) {
            [$studentId, $bookId] = array_map('intval', explode('_', $key));
            $assignments[$studentId][] = $bookId;
        }

        try {
            $count = $books->issueToStudents(Auth::user(), $this->school(), $assignments, $this->condition);
        } catch (ManualTrackingException $exception) {
            return $this->alert('error', $exception->getMessage());
        }

        $this->selected = [];
        $this->emit('schoolRecordsChanged');
        $this->alert('success', "Issued {$count} " . ($count === 1 ? 'book.' : 'books.'));
    }

    public function startReturn($issueId)
    {
        $this->returningIssueId = (int) $issueId;
        $this->returnCondition = 'good';
    }

    public function cancelReturn()
    {
        $this->returningIssueId = null;
    }

    public function confirmReturn(SchoolBookService $books)
    {
        $issue = StudentBookIssue::query()
            ->whereHas('student', fn ($student) => $student->where('organization_id', $this->schoolId))
            ->findOrFail($this->returningIssueId);

        try {
            $books->returnFromStudent(Auth::user(), $issue, $this->returnCondition);
        } catch (ManualTrackingException $exception) {
            return $this->alert('error', $exception->getMessage());
        }

        $this->returningIssueId = null;
        $this->emit('schoolRecordsChanged');
        $this->alert('success', 'Return recorded.');
    }

    private function scanFeedback(bool $ok, ?string $error = null): void
    {
        $this->dispatchBrowserEvent('scan-feedback', ['ok' => $ok]);

        if ($error) {
            $this->alert('error', $error);
        }
    }

    private function gradeIds()
    {
        return Student::query()->active()
            ->where('organization_id', $this->schoolId)
            ->where('academic_year', $this->year)
            ->distinct()
            ->pluck('grade_id');
    }

    private function students()
    {
        return Student::query()->active()
            ->where('organization_id', $this->schoolId)
            ->where('academic_year', $this->year)
            ->where('grade_id', $this->gradeId ?: 0)
            ->when($this->section, fn ($query) => $query->where('section', $this->section))
            ->orderBy('section')
            ->orderBy('name')
            ->get(['id', 'student_no', 'name', 'section']);
    }

    // Titles printed for this grade and the school's region
    private function titles($school)
    {
        return ManualBook::query()
            ->where('grade_id', $this->gradeId ?: 0)
            ->where(fn ($query) => $query->whereNull('region_id')->orWhere('region_id', $school->region_id))
            ->orderBy('title')
            ->get(['id', 'title']);
    }

    protected function permissionMap(): array
    {
        return [
            'view' => 'manual-tracking.schools.view',
            'actions' => [
                'scan' => 'manual-tracking.schools.issue',
                'issue' => 'manual-tracking.schools.issue',
                'startReturn' => 'manual-tracking.schools.issue',
                'confirmReturn' => 'manual-tracking.schools.issue',
            ],
        ];
    }
}
