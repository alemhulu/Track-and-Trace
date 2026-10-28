<?php

namespace App\Http\Livewire\School;

use App\Http\Livewire\School\Concerns\LoadsSchool;
use App\Models\ManualTracking\ManualBook;
use App\Models\ManualTracking\ManualStockBalance;
use App\Models\School\TeacherGuideIssue;
use App\Services\ManualTracking\ManualTrackingException;
use App\Services\School\SchoolBookService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class TeacherGuides extends Component
{
    use LoadsSchool;

    public $form = ['manual_book_id' => '', 'teacher_name' => '', 'section' => '', 'quantity' => 1];

    public function mount()
    {
        $this->school();
    }

    public function render()
    {
        $school = $this->school();
        $titles = ManualBook::query()
            ->where('has_teacher_guide', true)
            ->where(fn ($query) => $query->whereNull('region_id')->orWhere('region_id', $school->region_id))
            ->orderBy('title')
            ->get(['id', 'title']);

        return view('livewire.school.teacher-guides', [
            'titles' => $titles,
            'onHand' => ManualStockBalance::query()
                ->where('organization_id', $school->id)
                ->where('edition', ManualBook::EDITION_TEACHER)
                ->pluck('on_hand', 'manual_book_id'),
            'issues' => TeacherGuideIssue::query()
                ->where('organization_id', $school->id)
                ->where('academic_year', $this->year)
                ->with('book:id,title')
                ->orderByRaw('returned_at IS NOT NULL')
                ->orderByDesc('issued_at')
                ->get(),
        ]);
    }

    public function issue(SchoolBookService $books)
    {
        $this->validate([
            'form.manual_book_id' => 'required|exists:manual_books,id',
            'form.teacher_name' => 'required|string|max:255',
            'form.section' => 'nullable|string|max:10',
            'form.quantity' => 'required|integer|min:1|max:50',
        ], [], ['form.manual_book_id' => 'title', 'form.teacher_name' => 'teacher']);

        try {
            $books->issueTeacherGuides(
                Auth::user(),
                $this->school(),
                ManualBook::query()->findOrFail($this->form['manual_book_id']),
                (int) $this->year,
                $this->form['teacher_name'],
                strtoupper(trim($this->form['section'])) ?: null,
                (int) $this->form['quantity']
            );
        } catch (ManualTrackingException $exception) {
            return $this->alert('error', $exception->getMessage());
        }

        $this->reset('form');
        $this->emit('schoolRecordsChanged');
        $this->alert('success', 'Teacher guide issued.');
    }

    public function returnGuides($id, SchoolBookService $books)
    {
        $issue = TeacherGuideIssue::query()->where('organization_id', $this->schoolId)->findOrFail($id);

        try {
            $books->returnTeacherGuides(Auth::user(), $issue);
        } catch (ManualTrackingException $exception) {
            return $this->alert('error', $exception->getMessage());
        }

        $this->emit('schoolRecordsChanged');
        $this->alert('success', 'Return recorded.');
    }
}
