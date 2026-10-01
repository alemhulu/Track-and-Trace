<?php

namespace App\Http\Livewire\Book\Subject;

use App\Models\Subject;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class AddSubject extends Component
{
    use WithPagination;

    public $recordes = 5;
    public $column = 'name';
    public $sortType = 'asc';

    public $editingSubjectId = null;
    public $name = '';
    public $code = '';
    public $description = '';
    public $deleteId = '';

    protected function rules()
    {
        return [
            'name' => [
                'required',
                'min:2',
                'max:50',
                Rule::unique('subjects', 'name')->ignore($this->editingSubjectId),
            ],
            'code' => 'nullable|max:50',
            'description' => 'nullable|max:255',
        ];
    }

    public function updatedSearch()
    {
        $this->column = 'name';
        $this->sortType = 'asc';
        $this->resetPage();
    }

    public function updated()
    {
        $this->resetPage();
    }

    public function sort($value)
    {
        if ($this->column === $value && $this->sortType === 'asc') {
            $this->sortType = 'desc';
        } else {
            $this->column = $value;
            $this->sortType = 'asc';
        }

        $this->resetPage();
    }

    public function mount()
    {
        $this->resetFields();
    }

    public function render()
    {
        return view('livewire.book.subject.add-subject', [
            'subjects' => Subject::query()
                ->with('grades')
                ->when($this->column, function ($query) {
                    return $query->orderBy($this->column, $this->sortType);
                })
                ->paginate($this->recordes),
        ]);
    }

    public function addSubject()
    {
        $this->validate();

        $payload = [
            'name' => $this->name,
            'code' => $this->code,
            'description' => $this->description,
        ];

        if ($this->editingSubjectId) {
            $subject = Subject::find($this->editingSubjectId);
            if (! $subject) {
                return $this->alertError('Subject not found');
            }

            $subject->update($payload);
            $this->alertSuccess('Subject updated successfully!');
        } else {
            Subject::create($payload);
            $this->alertSuccess('Subject added successfully!');
        }

        $this->resetFields();
        $this->resetPage();
    }

    public function editSubject($id)
    {
        $subject = Subject::find($id);
        if (! $subject) {
            return $this->alertError('Subject not found');
        }

        $this->editingSubjectId = $subject->id;
        $this->name = $subject->name;
        $this->code = $subject->code;
        $this->description = $subject->description;
    }

    public function deleteSubject($id)
    {
        $subject = Subject::find($id);
        if (! $subject) {
            return $this->alertError('Subject not found');
        }

        if ($subject->grades()->exists() || $subject->books()->exists() || $subject->packages()->exists()) {
            return $this->alertError('Subject cannot be deleted, it has related data.');
        }

        $subject->delete();
        $this->alertDelete('Subject deleted successfully!');
        $this->resetPage();
    }

    public function clearid()
    {
        $this->resetFields();
    }

    protected function resetFields()
    {
        $this->editingSubjectId = null;
        $this->name = '';
        $this->code = '';
        $this->description = '';
        $this->deleteId = '';
    }

    public function alertError($name)
    {
        $this->dispatchBrowserEvent('alert', [
            'type' => 'error',
            'message' => $name,
        ]);
    }

    public function alertSuccess($message = 'Operation completed successfully!')
    {
        $this->dispatchBrowserEvent('alert', [
            'type' => 'success',
            'message' => $message,
        ]);
    }

    public function alertDelete($message = 'Item deleted successfully!')
    {
        $this->dispatchBrowserEvent('alert', [
            'type' => 'success',
            'message' => $message,
        ]);
    }
}
