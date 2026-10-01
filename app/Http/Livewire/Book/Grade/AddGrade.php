<?php

namespace App\Http\Livewire\Book\Grade;

use App\Models\Grade;
use App\Models\Subject;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;

class AddGrade extends Component
{
    public $subjects = [];

    public $editingGradeId = null;
    public $name = '';
    public $code = '';
    public $description = '';
    public $subjectIds = [];
    public $deleteId = '';

    protected function rules()
    {
        return [
            'name' => [
                'required',
                'min:1',
                'max:50',
                Rule::unique('grades', 'name')->ignore($this->editingGradeId),
            ],
            'code' => 'nullable|max:50',
            'description' => 'nullable|max:255',
            'subjectIds' => 'array',
            'subjectIds.*' => 'exists:subjects,id',
        ];
    }

    public function mount()
    {
        $this->subjects = Subject::orderBy('name')->get();
    }

    public function render()
    {
        return view('livewire.book.grade.add-grade', [
            'gradeList' => Grade::query()->with('subjects')->withCount('books')->latest()->get(),
            'subjects' => $this->subjects,
        ]);
    }

    public function addGrade()
    {
        $this->validate();

        $payload = [
            'name' => $this->name,
            'code' => $this->code,
            'description' => $this->description,
        ];

        try {
            DB::transaction(function () use ($payload) {
                if ($this->editingGradeId) {
                    $grade = Grade::find($this->editingGradeId);
                    if (! $grade) {
                        throw new \RuntimeException('Grade not found');
                    }

                    $grade->update($payload);
                    $grade->subjects()->sync($this->subjectIds);
                } else {
                    $grade = Grade::create($payload);
                    $grade->subjects()->sync($this->subjectIds);
                }
            });
        } catch (\RuntimeException $exception) {
            return $this->alertError($exception->getMessage());
        }

        $this->alertSuccess($this->editingGradeId ? 'Grade updated successfully!' : 'Grade added successfully!');

        $this->resetFields();
    }

    public function editGrade($id)
    {
        $grade = Grade::with('subjects')->find($id);
        if (! $grade) {
            return $this->alertError('Grade not found');
        }

        $this->editingGradeId = $grade->id;
        $this->name = $grade->name;
        $this->code = $grade->code;
        $this->description = $grade->description;
        $this->subjectIds = $grade->subjects->pluck('id')->all();
    }

    public function deleteGrade($id)
    {
        $grade = Grade::withCount('books')->with('subjects')->find($id);
        if (! $grade) {
            return $this->alertError('Grade not found');
        }

        if ($grade->books_count > 0 || $grade->subjects->count() > 0) {
            return $this->alertError('Grade cannot be deleted, it has related data.');
        }

        $grade->delete();
        $this->alertDelete('Grade deleted successfully!');
    }

    public function clearid()
    {
        $this->resetFields();
    }

    protected function resetFields()
    {
        $this->editingGradeId = null;
        $this->name = '';
        $this->code = '';
        $this->description = '';
        $this->subjectIds = [];
        $this->deleteId = '';
    }

    public function alertError($message)
    {
        $this->dispatchBrowserEvent('alert', [
            'type' => 'error',
            'message' => $message,
        ]);
    }

    public function alertSuccess($message)
    {
        $this->dispatchBrowserEvent('alert', [
            'type' => 'success',
            'message' => $message,
        ]);
    }

    public function alertDelete($message)
    {
        $this->dispatchBrowserEvent('alert', [
            'type' => 'success',
            'message' => $message,
        ]);
    }
}
