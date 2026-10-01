<?php

namespace App\Http\Livewire\Book;

use App\Models\Book;
use App\Models\BookType;
use App\Models\Grade;
use App\Models\PaperSize;
use App\Models\PrintType;
use Livewire\Component;
use Livewire\WithFileUploads;

class AddBook extends Component
{
    use WithFileUploads;
    //variables
    public $grades = [], $subjects = [], $bookTypes = [], $printTypes = [], $paperSizes = [];
    public $grade_id, $subject_id, $book_type, $edition, $volume, $isbn, $paper_size, $file, $front_cover, $back_cover, $print_type;
    public $editingBookId = null;
    public $existingFileLocation = null;
    public $existingFrontCoverLocation = null;
    public $existingBackCoverLocation = null;

    protected function rules()
    {
        $isEditing = ! empty($this->editingBookId);
        $bookTypeValues = BookType::query()->pluck('code')->filter(function ($value) {
            return $value !== null && $value !== '';
        })->values()->all();
        $printTypeValues = PrintType::query()->pluck('code')->filter(function ($value) {
            return $value !== null && $value !== '';
        })->values()->all();
        $paperSizeValues = PaperSize::query()->pluck('code')->filter(function ($value) {
            return $value !== null && $value !== '';
        })->values()->all();

        return [
            'grade_id' => 'required|exists:grades,id',
            'subject_id' => 'required|exists:subjects,id',
            'book_type' => 'required|' . 'in:' . implode(',', $bookTypeValues ?: ['0', '1']),
            'edition' => 'required',
            'isbn' => 'required',
            'print_type' => 'required|' . 'in:' . implode(',', $printTypeValues ?: ['0', '1']),
            'paper_size' => 'required|' . 'in:' . implode(',', $paperSizeValues ?: ['A4', 'A5']),
            'file' => ($isEditing ? 'nullable' : 'required') . '|file|mimes:pdf',
            'front_cover' => ($isEditing ? 'nullable' : 'required') . '|image',
            'back_cover' => ($isEditing ? 'nullable' : 'required') . '|image',
        ];
    }

    public function mount()
    {
        $this->grades = Grade::all();
        $this->bookTypes = BookType::query()->orderBy('name')->get();
        $this->printTypes = PrintType::query()->orderBy('name')->get();
        $this->paperSizes = PaperSize::query()->orderBy('name')->get();
        $editId = request()->query('edit');
        if ($editId) {
            $this->loadBookForEdit($editId);
        }
    }

    public function render()
    {
        return view('livewire.book.add-book')->extends('main.book.index');
    }

    public function updatedGradeId()
    {
        $this->subject_id = null;
        $grade = Grade::find($this->grade_id);
        $this->subjects = $grade ? $grade->subjects : collect();
    }

    public function loadBookForEdit($id)
    {
        $book = Book::find($id);
        if (! $book) {
            $this->alertError('Book not found');
            return;
        }

        $this->editingBookId = $book->id;
        $this->grade_id = $book->grade_id;
        $this->updatedGradeId();
        $this->subject_id = $book->subject_id;
        $this->book_type = (string) $book->book_type;
        $this->print_type = (string) $book->print_type;
        $this->paper_size = $book->paper_size;
        $this->edition = $book->edition;
        $this->volume = $book->volume;
        $this->isbn = $book->isbn;
        $this->existingFileLocation = $book->file_location;
        $this->existingFrontCoverLocation = $book->front_cover_location;
        $this->existingBackCoverLocation = $book->back_cover_location;
    }

    public function addBook()
    {
        $this->validate();
        $data = [
            'grade_id' => $this->grade_id,
            'subject_id' => $this->subject_id,
            'book_type' => $this->book_type,
            'print_type' => $this->print_type,
            'paper_size' => $this->paper_size,
            'edition' => $this->edition,
            'volume' => $this->volume,
            'isbn' => $this->isbn,
        ];

        $book = $this->editingBookId ? Book::find($this->editingBookId) : Book::create($data);
        if (! $book) {
            return $this->alertError('Book not found');
        }

        if ($this->editingBookId) {
            $book->update($data);
        }

        if ($this->front_cover) {
            $frontCoverFileName = $this->front_cover->getClientOriginalName();
            $frontCoverFilePath = $this->front_cover->storeAs('book/' . $book->id, $frontCoverFileName, 'public');
            $book->front_cover_location = '/storage/' . $frontCoverFilePath;
        }

        if ($this->back_cover) {
            $backCoverFileName = $this->back_cover->getClientOriginalName();
            $backCoverFilePath = $this->back_cover->storeAs('book/' . $book->id, $backCoverFileName, 'public');
            $book->back_cover_location = '/storage/' . $backCoverFilePath;
        }

        if ($this->file) {
            $fileFileName = $this->file->getClientOriginalName();
            $fileFilePath = $this->file->storeAs('book/' . $book->id, $fileFileName, 'public');
            $book->file_location = '/storage/' . $fileFilePath;
        }

        $book->save();

        if ($this->editingBookId) {
            $this->alertSuccess('Book updated successfully!');
            return redirect()->route('book.list');
        }

        $this->alertSuccess('Book added successfully!');
        $this->resetForm();
    }

    protected function resetForm()
    {
        $this->grade_id = null;
        $this->subject_id = null;
        $this->subjects = [];
        $this->book_type = null;
        $this->edition = null;
        $this->volume = null;
        $this->isbn = null;
        $this->paper_size = null;
        $this->file = null;
        $this->front_cover = null;
        $this->back_cover = null;
        $this->print_type = null;
        $this->editingBookId = null;
        $this->existingFileLocation = null;
        $this->existingFrontCoverLocation = null;
        $this->existingBackCoverLocation = null;
    }

    public function alertError($name)
    {
        $this->dispatchBrowserEvent(
            'alert',
            ['type' => 'error', 'message' => $name]
        );
    }

    public function alertSuccess($message = 'Book added successfully!')
    {
        $this->dispatchBrowserEvent(
            'alert',
            ['type' => 'success', 'message' => $message]
        );
    }

    public function alertWarning()
    {
        $this->dispatchBrowserEvent(
            'alert',
            ['type' => 'warning', 'message' => 'Book will be deleted after save !!']
        );
    }
}
