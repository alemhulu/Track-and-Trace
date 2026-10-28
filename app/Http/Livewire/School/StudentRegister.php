<?php

namespace App\Http\Livewire\School;

use App\Http\Livewire\School\Concerns\LoadsSchool;
use App\Models\Grade;
use App\Models\School\Student;
use App\Services\ManualTracking\ManualTrackingException;
use App\Services\School\SchoolBookService;
use App\Services\School\StudentImportService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class StudentRegister extends Component
{
    use LoadsSchool;
    use WithFileUploads;
    use WithPagination;

    public $gradeFilter = '';
    public $search = '';
    public $csv;
    public $importErrors = [];
    public $editingId;
    public $form = ['student_no' => '', 'name' => '', 'sex' => '', 'grade_id' => '', 'section' => '', 'status' => Student::STATUS_ACTIVE];

    public function mount()
    {
        $this->school();
    }

    public function updatingGradeFilter()
    {
        $this->resetPage();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $students = Student::query()
            ->where('organization_id', $this->schoolId)
            ->where('academic_year', $this->year)
            ->with('grade:id,name')
            ->withCount('heldBooks')
            ->when($this->gradeFilter, fn ($query) => $query->where('grade_id', $this->gradeFilter))
            ->when($this->search, fn ($query) => $query->where(fn ($match) => $match
                ->where('name', 'like', '%' . $this->search . '%')
                ->orWhere('student_no', 'like', '%' . $this->search . '%')))
            ->orderBy('grade_id')
            ->orderBy('section')
            ->orderBy('name')
            ->paginate(25);

        return view('livewire.school.student-register', [
            'students' => $students,
            'grades' => Grade::query()->orderByRaw('CAST(name AS UNSIGNED)')->get(['id', 'name']),
        ]);
    }

    public function saveStudent(SchoolBookService $books)
    {
        $data = $this->validate([
            'form.student_no' => ['required', 'string', 'max:50', Rule::unique('students', 'student_no')
                ->where('organization_id', $this->schoolId)
                ->where('academic_year', $this->year)
                ->ignore($this->editingId)],
            'form.name' => 'required|string|max:255',
            'form.sex' => 'required|in:M,F',
            'form.grade_id' => 'required|exists:grades,id',
            'form.section' => 'nullable|string|max:10',
            'form.status' => ['required', Rule::in(array_keys(Student::STATUSES))],
        ], [], [
            'form.student_no' => 'student number',
            'form.grade_id' => 'grade',
        ])['form'];

        if (! $this->authorized($books)) {
            return;
        }

        $data['section'] = strtoupper(trim((string) $data['section'])) ?: null;

        if ($this->editingId) {
            Student::query()->where('organization_id', $this->schoolId)->findOrFail($this->editingId)->update($data);
        } else {
            Student::query()->create($data + ['organization_id' => $this->schoolId, 'academic_year' => $this->year]);
        }

        $this->cancelEdit();
        $this->emit('schoolRecordsChanged');
        $this->alert('success', 'Student saved.');
    }

    public function editStudent($id)
    {
        $student = Student::query()->where('organization_id', $this->schoolId)->findOrFail($id);
        $this->editingId = $student->id;
        $this->form = $student->only(['student_no', 'name', 'sex', 'grade_id', 'section', 'status']);
    }

    public function cancelEdit()
    {
        $this->reset('editingId', 'form');
        $this->resetValidation();
    }

    public function import(StudentImportService $importer)
    {
        $this->validate(['csv' => 'required|file|mimes:csv,txt|max:4096'], [], ['csv' => 'CSV file']);

        try {
            $result = $importer->import(Auth::user(), $this->school(), (int) $this->year, $this->csv->getRealPath());
        } catch (ManualTrackingException $exception) {
            return $this->alert('error', $exception->getMessage());
        }

        $this->importErrors = $result['errors'];
        $this->reset('csv');

        if ($result['errors']) {
            return $this->alert('error', 'Nothing was imported. Fix the rows listed below and upload again.');
        }

        $this->emit('schoolRecordsChanged');
        $this->alert('success', "Imported {$result['created']} new and updated {$result['updated']} students.");
    }

    private function authorized(SchoolBookService $books): bool
    {
        try {
            $books->authorize(Auth::user(), $this->school());

            return true;
        } catch (ManualTrackingException $exception) {
            $this->alert('error', $exception->getMessage());

            return false;
        }
    }
}
