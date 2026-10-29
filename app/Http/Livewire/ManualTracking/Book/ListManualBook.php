<?php

namespace App\Http\Livewire\ManualTracking\Book;

use App\Http\Livewire\Concerns\AuthorizesPermissions;
use App\Models\ManualTracking\ManualAllocation;
use App\Models\ManualTracking\ManualBook;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class ListManualBook extends Component
{
    use WithPagination;
    use AuthorizesPermissions;

    public $recordes = 10;
    public $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingRecordes()
    {
        $this->resetPage();
    }

    public function render()
    {
        $year = ManualAllocation::latestYear();

        $books = ManualBook::query()
            ->accessibleBy(Auth::user())
            ->when(trim($this->search) !== '', fn ($query) => $query->where(fn ($match) => $match
                ->where('title', 'like', '%' . trim($this->search) . '%')
                ->orWhere('grade_name', 'like', '%' . trim($this->search) . '%')
                ->orWhere('subject_name', 'like', '%' . trim($this->search) . '%')
                ->orWhere('language', 'like', '%' . trim($this->search) . '%')))
            ->with(['region:id,name', 'codes'])
            ->withSum(['balances as student_on_hand' => fn ($query) => $query->where('edition', ManualBook::EDITION_STUDENT)], 'on_hand')
            ->withSum(['balances as teacher_on_hand' => fn ($query) => $query->where('edition', ManualBook::EDITION_TEACHER)], 'on_hand')
            ->withSum(['allocations as student_allocated' => fn ($query) => $query->where('academic_year', $year)->whereNull('zone_id')->where('edition', ManualBook::EDITION_STUDENT)], 'quantity')
            ->withSum(['allocations as teacher_allocated' => fn ($query) => $query->where('academic_year', $year)->whereNull('zone_id')->where('edition', ManualBook::EDITION_TEACHER)], 'quantity')
            ->orderBy('title')
            ->paginate($this->recordes);

        return view('livewire.manual-tracking.book.list-manual-book', [
            'books' => $books,
            'canCreate' => Auth::user()->canManageBookTitles('create'),
            'canEdit' => Auth::user()->canManageBookTitles('edit'),
            'canDelete' => Auth::user()->canManageBookTitles('delete'),
        ])->extends('main.manual-tracking.index');
    }

    public function editBook($id)
    {
        abort_unless(Auth::user()->canManageBookTitles('edit'), 403);

        return redirect()->route('manual-tracking.books.add', ['edit' => $id]);
    }

    public function deleteBook($id)
    {
        if (! Auth::user()->canManageBookTitles('delete')) {
            return $this->alertError('You are not allowed to delete titles.');
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

    protected function permissionMap(): array
    {
        return [
            'view' => 'manual-tracking.view',
            'actions' => [
                'editBook' => 'manual-tracking.titles.edit',
                'deleteBook' => 'manual-tracking.titles.delete',
                'deleteId' => 'manual-tracking.titles.delete',
            ],
        ];
    }
}
