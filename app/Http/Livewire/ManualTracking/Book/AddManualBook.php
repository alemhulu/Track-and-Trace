<?php

namespace App\Http\Livewire\ManualTracking\Book;

use App\Models\ManualTracking\ManualBook;
use Livewire\Component;

class AddManualBook extends Component
{
    public $code;
    public $title;
    public $grade_name;
    public $subject_name;
    public $isbn;
    public $edition;
    public $total_copies = 0;
    public $notes;
    public $editingBookId;

    protected function rules()
    {
        $bookId = $this->editingBookId;

        return [
            'code' => 'nullable|string|max:255|unique:manual_books,code,' . $bookId,
            'title' => 'required|string|max:255',
            'grade_name' => 'nullable|string|max:255',
            'subject_name' => 'nullable|string|max:255',
            'isbn' => 'nullable|string|max:255',
            'edition' => 'nullable|string|max:255',
            'total_copies' => 'required|integer|min:0',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    public function mount()
    {
        $editId = request()->query('edit');
        if ($editId) {
            $this->loadForEdit((int) $editId);
        }
    }

    public function render()
    {
        return view('livewire.manual-tracking.book.add-manual-book')->extends('main.manual-tracking.index');
    }

    public function saveBook()
    {
        $data = $this->validate();

        if ($this->editingBookId) {
            $book = ManualBook::query()->find($this->editingBookId);
            if (! $book) {
                return $this->alertError('Manual book not found.');
            }

            $book->update($data);
            $this->alertSuccess('Manual book updated successfully.');
            return redirect()->route('manual-tracking.books.list');
        }

        ManualBook::query()->create($data);
        $this->resetForm();
        $this->alertSuccess('Manual book created successfully.');
    }

    private function loadForEdit(int $id): void
    {
        $book = ManualBook::query()->find($id);
        if (! $book) {
            $this->alertError('Manual book not found.');
            return;
        }

        $this->editingBookId = $book->id;
        $this->code = $book->code;
        $this->title = $book->title;
        $this->grade_name = $book->grade_name;
        $this->subject_name = $book->subject_name;
        $this->isbn = $book->isbn;
        $this->edition = $book->edition;
        $this->total_copies = $book->total_copies;
        $this->notes = $book->notes;
    }

    private function resetForm(): void
    {
        $this->code = null;
        $this->title = null;
        $this->grade_name = null;
        $this->subject_name = null;
        $this->isbn = null;
        $this->edition = null;
        $this->total_copies = 0;
        $this->notes = null;
        $this->editingBookId = null;
    }

    private function alertError($message)
    {
        $this->dispatchBrowserEvent('alert', [
            'type' => 'error',
            'message' => $message,
        ]);
    }

    private function alertSuccess($message)
    {
        $this->dispatchBrowserEvent('alert', [
            'type' => 'success',
            'message' => $message,
        ]);
    }
}
