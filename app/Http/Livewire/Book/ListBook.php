<?php

namespace App\Http\Livewire\Book;

use App\Models\Book;
use Livewire\Component;
use Livewire\WithPagination;
class ListBook extends Component
{ 
    use WithPagination;
    //variables
    public $column, $recordes, $sortType;
    public function mount()
    {
        $this->recordes=5;
        $this->column='';
    }
    public function render()
    {
        return view('livewire.book.list-book',
        ['books'=>Book::query()
        ->with(['grade', 'subject'])
        ->withCount('printOrder')
        ->withSum('printOrder', 'no_of_books')
        ->when($this->column,function($q,$column){
            return $q->orderBy($this->column,$this->sortType);
        })->paginate($this->recordes)

    ])->extends('main.book.index');
    }

    public function editBook($id)
    {
        $book = Book::find($id);
        if (! $book) {
            return $this->alertError('Book not found');
        }

        return redirect()->route('book.add', ['edit' => $book->id]);
    }

    public function deleteBook($id)
    {
        $book = Book::withCount('printOrder')->find($id);
        if (! $book) {
            return $this->alertError('Book not found');
        }

        if ($book->print_order_count > 0) {
            return $this->alertError('Book cannot be deleted, it has related print orders.');
        }

        $book->delete();
        $this->dispatchBrowserEvent('alert', [
            'type' => 'success',
            'message' => 'Book deleted successfully!',
        ]);
    }

    // Kept for compatibility with x-action.table-button delete click behavior.
    public function deleteId($id)
    {
        return $this->deleteBook($id);
    }

    public function alertError($name)
    {
        $this->dispatchBrowserEvent('alert', [
            'type' => 'error',
            'message' => $name,
        ]);
    }

    public function clearid() {}
}
