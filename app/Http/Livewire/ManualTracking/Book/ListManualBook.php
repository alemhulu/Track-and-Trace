<?php

namespace App\Http\Livewire\ManualTracking\Book;

use App\Models\ManualTracking\ManualBook;
use Livewire\Component;
use Livewire\WithPagination;

class ListManualBook extends Component
{
    use WithPagination;

    public $recordes = 10;

    public function render()
    {
        $books = ManualBook::query()
            ->withCount('packages')
            ->orderByDesc('id')
            ->paginate($this->recordes);

        return view('livewire.manual-tracking.book.list-manual-book', [
            'books' => $books,
        ])->extends('main.manual-tracking.index');
    }

    public function editBook($id)
    {
        return redirect()->route('manual-tracking.books.add', ['edit' => $id]);
    }

    public function deleteBook($id)
    {
        $book = ManualBook::query()->withCount('packages')->find($id);
        if (! $book) {
            return $this->alertError('Manual book not found.');
        }

        if ($book->packages_count > 0) {
            return $this->alertError('Book has related packages and cannot be deleted.');
        }

        $book->delete();
        $this->alertSuccess('Manual book deleted successfully.');
    }

    public function deleteId($id)
    {
        return $this->deleteBook($id);
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
