<?php

namespace App\Http\Livewire\ManualTracking\Book;

use App\Models\ManualTracking\ManualBook;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class ListManualBook extends Component
{
    use WithPagination;

    public $recordes = 10;

    public function render()
    {
        $books = ManualBook::query()
            ->accessibleBy(Auth::user())
            ->with(['region:id,name', 'codes'])
            ->withSum(['balances as student_on_hand' => fn ($query) => $query->where('edition', ManualBook::EDITION_STUDENT)], 'on_hand')
            ->withSum(['balances as teacher_on_hand' => fn ($query) => $query->where('edition', ManualBook::EDITION_TEACHER)], 'on_hand')
            ->orderBy('title')
            ->paginate($this->recordes);

        return view('livewire.manual-tracking.book.list-manual-book', [
            'books' => $books,
            'canManage' => Auth::user()->canManageBookTitles(),
        ])->extends('main.manual-tracking.index');
    }

    public function editBook($id)
    {
        abort_unless(Auth::user()->canManageBookTitles(), 403);

        return redirect()->route('manual-tracking.books.add', ['edit' => $id]);
    }

    public function deleteBook($id)
    {
        if (! Auth::user()->canManageBookTitles()) {
            return $this->alertError('Only administrators can delete titles.');
        }

        $book = ManualBook::query()->withCount(['stockLedgers', 'distributionLines'])->find($id);
        if (! $book) {
            return $this->alertError('Title not found.');
        }

        // Titles with history stay, so the ledger and shipments remain explainable
        if ($book->stock_ledgers_count > 0 || $book->distribution_lines_count > 0) {
            return $this->alertError('This title has stock or shipment history and cannot be deleted.');
        }

        $book->delete();
        $this->alertSuccess('Title deleted successfully.');
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
