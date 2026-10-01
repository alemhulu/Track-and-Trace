<?php

namespace App\Http\Livewire\Print;

use App\Models\PrintOrder;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

use Livewire\WithPagination;
class ListPrintOrder extends Component
{
    use WithPagination;
    //variables
    public $recordes = 10;
    public $sortType = 'desc';

    // Search / filter variables
    public $search = '';
    public $statusFilter = '';
    public $createdFrom = '';
    public $createdTo = '';
    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'createdFrom' => ['except' => ''],
        'createdTo' => ['except' => ''],
        'sortType' => ['except' => 'desc'],
        'recordes' => ['except' => 10],
    ];

    public function mount()
    {
        $this->sortType = 'desc';
    }

    public function render()
    {
        $actor = auth()->user();
        if ($actor && Gate::denies('viewAny', PrintOrder::class)) {
            abort(403, 'You are not authorized to view print orders.');
        }

        return view('livewire.print.list-print-order', [
            'orders' => PrintOrder::query()
                ->with([
                    'book.grade',
                    'book.subject',
                    'orderOrganization',
                    'printOrganization',
                ])
                ->when($actor, function ($query) use ($actor) {
                    $query->accessibleBy($actor);
                })
                ->when($this->search, function ($query) {
                    $search = trim($this->search);

                    $query->where(function ($searchQuery) use ($search) {
                        $searchQuery
                            ->where('id', 'like', "%{$search}%")
                            ->orWhere('no_of_books', 'like', "%{$search}%")
                            ->orWhereHas('book', function ($bookQuery) use ($search) {
                                $bookQuery
                                    ->where('isbn', 'like', "%{$search}%")
                                    ->orWhereHas('grade', function ($gradeQuery) use ($search) {
                                        $gradeQuery->where('name', 'like', "%{$search}%");
                                    })
                                    ->orWhereHas('subject', function ($subjectQuery) use ($search) {
                                        $subjectQuery->where('name', 'like', "%{$search}%");
                                    });
                            })
                            ->orWhereHas('orderOrganization', function ($organizationQuery) use ($search) {
                                $organizationQuery
                                    ->where('name', 'like', "%{$search}%")
                                    ->orWhere('email', 'like', "%{$search}%");
                            })
                            ->orWhereHas('printOrganization', function ($organizationQuery) use ($search) {
                                $organizationQuery
                                    ->where('name', 'like', "%{$search}%")
                                    ->orWhere('email', 'like', "%{$search}%");
                            });
                    });
                })
                ->when($this->statusFilter !== '', function ($query) {
                    $query->where('request_status', (int) $this->statusFilter);
                })
                ->when($this->createdFrom, function ($query) {
                    $query->whereDate('created_at', '>=', $this->createdFrom);
                })
                ->when($this->createdTo, function ($query) {
                    $query->whereDate('created_at', '<=', $this->createdTo);
                })
                ->orderBy('created_at', $this->sortType)
                ->paginate($this->recordes),
        ])->extends('main.print-order.index');
    }

    // Reset pagination on every variable updated
    public function updated()
    {
        $this->resetPage();
    }
}
