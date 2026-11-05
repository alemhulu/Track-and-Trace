<?php

namespace App\Http\Livewire\ManualTracking\Book;

use App\Http\Livewire\Concerns\AuthorizesPermissions;
use App\Models\ManualTracking\ManualAllocation;
use App\Models\ManualTracking\ManualBook;
use App\Models\ManualTracking\ManualStockBalance;
use App\Models\Region;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class ListManualBook extends Component
{
    use WithPagination;
    use AuthorizesPermissions;

    public $recordes = 10;
    public $search = '';

    // Filters; region is a region id or 'all' for titles used in every region
    public $filters = [
        'region' => '',
        'grade' => '',
        'subject' => '',
        'language' => '',
        'teacher_guide' => '',
        'codes' => '',
        'stock' => '',
        'allocation' => '',
    ];

    protected $queryString = ['search' => ['except' => '']];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilters()
    {
        $this->resetPage();
    }

    public function setFilter(string $key, string $value): void
    {
        if (array_key_exists($key, $this->filters)) {
            $this->filters[$key] = $this->filters[$key] === $value ? '' : $value;
            $this->resetPage();
        }
    }

    public function clearFilters(): void
    {
        $this->reset('filters', 'search');
        $this->resetPage();
    }

    public function updatingRecordes()
    {
        $this->resetPage();
    }

    public function render()
    {
        $year = ManualAllocation::currentYear();

        $books = $this->filtered($year)
            ->with(['region:id,name', 'codes'])
            ->withSum(['balances as student_on_hand' => fn ($query) => $query->where('edition', ManualBook::EDITION_STUDENT)], 'on_hand')
            ->withSum(['balances as teacher_on_hand' => fn ($query) => $query->where('edition', ManualBook::EDITION_TEACHER)], 'on_hand')
            ->withSum(['allocations as student_allocated' => fn ($query) => $query->where('academic_year', $year)->whereNull('zone_id')->where('edition', ManualBook::EDITION_STUDENT)], 'quantity')
            ->withSum(['allocations as teacher_allocated' => fn ($query) => $query->where('academic_year', $year)->whereNull('zone_id')->where('edition', ManualBook::EDITION_TEACHER)], 'quantity')
            ->withSum('distributionLines as student_sent', 'quantity')
            ->withSum('distributionLines as teacher_sent', 'teacher_guides')
            ->orderBy('title')
            ->paginate($this->recordes);

        $catalog = ManualBook::query()->accessibleBy(Auth::user());
        $options = fn (string $column) => (clone $catalog)->whereNotNull($column)->where($column, '!=', '')
            ->distinct()->orderBy($column)->pluck($column);

        return view('livewire.manual-tracking.book.list-manual-book', [
            'books' => $books,
            'stats' => $this->stats($year),
            'year' => $year,
            'regions' => Region::query()->whereIn('id', (clone $catalog)->whereNotNull('region_id')->select('region_id'))->orderBy('name')->get(['id', 'name']),
            'grades' => $options('grade_name'),
            'subjects' => $options('subject_name'),
            'languages' => $options('language'),
            'filtersActive' => trim($this->search) !== '' || collect($this->filters)->filter()->isNotEmpty(),
            'canCreate' => Auth::user()->canManageBookTitles('create'),
            'canEdit' => Auth::user()->canManageBookTitles('edit'),
            'canDelete' => Auth::user()->canManageBookTitles('delete'),
        ])->extends('main.manual-tracking.index');
    }

    /**
     * Titles the user can see, narrowed by the search and filters.
     */
    private function filtered(?string $year): Builder
    {
        $filter = fn (string $key) => (string) ($this->filters[$key] ?? '');

        return ManualBook::query()
            ->accessibleBy(Auth::user())
            ->when(trim($this->search) !== '', fn ($query) => $query->where(fn ($match) => $match
                ->where('title', 'like', '%' . trim($this->search) . '%')
                ->orWhere('grade_name', 'like', '%' . trim($this->search) . '%')
                ->orWhere('subject_name', 'like', '%' . trim($this->search) . '%')
                ->orWhere('language', 'like', '%' . trim($this->search) . '%')
                ->orWhere('code', 'like', '%' . trim($this->search) . '%')))
            ->when($filter('region') === 'all', fn ($query) => $query->whereNull('region_id'))
            ->when(! in_array($filter('region'), ['', 'all'], true), fn ($query) => $query->where('region_id', $filter('region')))
            ->when($filter('grade') !== '', fn ($query) => $query->where('grade_name', $filter('grade')))
            ->when($filter('subject') !== '', fn ($query) => $query->where('subject_name', $filter('subject')))
            ->when($filter('language') !== '', fn ($query) => $query->where('language', $filter('language')))
            ->when($filter('teacher_guide') !== '', fn ($query) => $query->where('has_teacher_guide', $filter('teacher_guide') === 'with'))
            ->when($filter('codes') === 'linked', fn ($query) => $query->has('codes'))
            ->when($filter('codes') === 'unlinked', fn ($query) => $query->doesntHave('codes'))
            ->when($filter('stock') === 'in_stock', fn ($query) => $query->whereHas('balances', fn ($balance) => $balance->where('on_hand', '>', 0)))
            ->when($filter('stock') === 'none', fn ($query) => $query->whereDoesntHave('balances', fn ($balance) => $balance->where('on_hand', '>', 0)))
            ->when($filter('allocation') === 'allocated', fn ($query) => $query->whereHas('allocations', fn ($allocation) => $allocation->where('academic_year', $year)))
            ->when($filter('allocation') === 'unallocated', fn ($query) => $query->whereDoesntHave('allocations', fn ($allocation) => $allocation->where('academic_year', $year)));
    }

    /**
     * Figures over every title matching the filters, not just the page shown.
     */
    private function stats(?string $year): array
    {
        $ids = fn () => $this->filtered($year)->select('manual_books.id');
        $onHand = ManualStockBalance::query()->whereIn('manual_book_id', $ids())
            ->selectRaw("SUM(CASE WHEN edition = 'student' THEN on_hand ELSE 0 END) as student")
            ->selectRaw("SUM(CASE WHEN edition = 'teacher' THEN on_hand ELSE 0 END) as teacher")
            ->selectRaw("SUM(CASE WHEN edition = 'student' THEN in_transit_out ELSE 0 END) as student_moving")
            ->selectRaw("SUM(CASE WHEN edition = 'teacher' THEN in_transit_out ELSE 0 END) as teacher_moving")
            ->first();
        $allocated = ManualAllocation::query()->whereIn('manual_book_id', $ids())->where('academic_year', $year)->whereNull('zone_id')
            ->selectRaw("SUM(CASE WHEN edition = 'student' THEN quantity ELSE 0 END) as student")
            ->selectRaw("SUM(CASE WHEN edition = 'teacher' THEN quantity ELSE 0 END) as teacher")
            ->first();

        return [
            'titles' => $this->filtered($year)->count(),
            'with_teacher_guide' => $this->filtered($year)->where('has_teacher_guide', true)->count(),
            'linked' => $this->filtered($year)->has('codes')->count(),
            'allocated_titles' => $this->filtered($year)->whereHas('allocations', fn ($allocation) => $allocation->where('academic_year', $year))->count(),
            'in_stock' => $this->filtered($year)->whereHas('balances', fn ($balance) => $balance->where('on_hand', '>', 0))->count(),
            'grades' => $this->filtered($year)->whereNotNull('grade_name')->distinct()->count('grade_name'),
            'languages' => $this->filtered($year)->whereNotNull('language')->distinct()->count('language'),
            'student_on_hand' => (int) ($onHand->student ?? 0),
            'teacher_on_hand' => (int) ($onHand->teacher ?? 0),
            'student_moving' => (int) ($onHand->student_moving ?? 0),
            'teacher_moving' => (int) ($onHand->teacher_moving ?? 0),
            'student_allocated' => (int) ($allocated->student ?? 0),
            'teacher_allocated' => (int) ($allocated->teacher ?? 0),
        ];
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
