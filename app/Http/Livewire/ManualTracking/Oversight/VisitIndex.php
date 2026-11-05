<?php

namespace App\Http\Livewire\ManualTracking\Oversight;

use App\Http\Livewire\Concerns\AuthorizesPermissions;
use App\Models\Organization;
use App\Models\Oversight\VerificationVisit;
use App\Support\AcademicYear;
use App\Support\ScopedLocation;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class VisitIndex extends Component
{
    use WithPagination;
    use AuthorizesPermissions;

    public $year;
    public $schoolId = '';
    public $search = '';
    public $area = ['region_id' => '', 'zone_id' => '', 'woreda_id' => ''];
    public $dateFrom = '';
    public $dateTo = '';
    // '' or one of RESULTS
    public $result = '';

    public const RESULTS = [
        'clean' => 'Everything matched',
        'shelf' => 'Shelf count differs',
        'students' => 'Students missing books',
    ];

    public function mount()
    {
        $this->year = AcademicYear::current();
        $this->area = $this->location()->apply([]);
    }

    public function updating($property)
    {
        if (in_array($property, ['year', 'search', 'dateFrom', 'dateTo', 'result'], true)) {
            $this->resetPage();
        }
    }

    public function updatedArea($value, $key)
    {
        $this->area = $this->location()->apply($this->area, $key);
        $this->resetPage();
    }

    public function setResult(string $result): void
    {
        $this->result = $this->result === $result || ! isset(self::RESULTS[$result]) ? '' : $result;
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset('search', 'dateFrom', 'dateTo', 'result');
        $this->area = $this->location()->apply([]);
        $this->resetPage();
    }

    public function render()
    {
        $actor = Auth::user();

        return view('livewire.manual-tracking.oversight.visit-index', [
            'visits' => $this->applyResult($this->filtered(), $this->result)
                ->with(['school:id,name,zone_id,woreda_id', 'school.zone:id,name', 'school.woreda:id,name', 'visitor:id,name'])
                ->latest('visited_at')
                ->paginate(15),
            // A school's own staff cannot verify it
            'schools' => Organization::query()
                ->accessibleBy($actor)
                ->whereHas('organizationType', fn ($type) => $type->where('name', 'School'))
                ->when($actor->organization_id, fn ($query) => $query->whereKeyNot($actor->organization_id))
                ->orderBy('name')
                ->get(['id', 'name']),
            'years' => AcademicYear::options(),
            'stats' => $this->stats(),
            'locationPicker' => $this->location()->picker($this->area, Organization::query()->whereRaw('1 = 0')),
            'filtersActive' => trim($this->search) !== '' || $this->dateFrom || $this->dateTo || $this->result !== ''
                || collect($this->area)->filter()->diffAssoc($this->location()->locked())->isNotEmpty(),
        ])->extends('main.manual-tracking.index');
    }

    /**
     * The year's visits the user can see, narrowed by place, school name and date.
     */
    private function filtered(): Builder
    {
        $search = trim($this->search);
        $date = fn ($value) => is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1;

        return VerificationVisit::query()
            ->accessibleBy(Auth::user())
            ->where('academic_year', $this->year)
            ->whereIn('organization_id', $this->location()->organizations($this->area)->select('id'))
            ->when($search !== '', fn ($query) => $query->where(fn ($match) => $match
                ->whereHas('school', fn ($school) => $school->where('name', 'like', '%' . $search . '%'))
                ->orWhereHas('visitor', fn ($visitor) => $visitor->where('name', 'like', '%' . $search . '%'))))
            ->when($date($this->dateFrom), fn ($query) => $query->where('visited_at', '>=', Carbon::parse($this->dateFrom)->startOfDay()))
            ->when($date($this->dateTo), fn ($query) => $query->where('visited_at', '<=', Carbon::parse($this->dateTo)->endOfDay()));
    }

    private function applyResult(Builder $query, string $result): Builder
    {
        return match ($result) {
            'shelf' => $query->whereColumn('stock_lines_matching', '<', 'stock_lines'),
            'students' => $query->whereColumn('books_seen', '<', 'books_expected'),
            'clean' => $query->whereColumn('stock_lines_matching', '>=', 'stock_lines')->whereColumn('books_seen', '>=', 'books_expected'),
            default => $query,
        };
    }

    /**
     * Figures over every matching visit, ignoring the result filter whose counts they show.
     */
    private function stats(): array
    {
        $totals = $this->filtered()->toBase()
            ->selectRaw('COUNT(*) as visits, COUNT(DISTINCT organization_id) as schools, COUNT(DISTINCT visited_by) as visitors')
            ->selectRaw('COALESCE(SUM(stock_lines), 0) as stock_lines, COALESCE(SUM(stock_lines_matching), 0) as stock_matching')
            ->selectRaw('COALESCE(SUM(books_expected), 0) as books_expected, COALESCE(SUM(books_seen), 0) as books_seen')
            ->selectRaw('COALESCE(SUM(students_checked), 0) as students')
            ->first();
        $schools = $this->location()->organizations($this->area)
            ->whereHas('organizationType', fn ($type) => $type->where('name', 'School'))->count();

        return [
            'visits' => (int) $totals->visits,
            'schools' => (int) $totals->schools,
            'school_total' => $schools,
            'visitors' => (int) $totals->visitors,
            'students' => (int) $totals->students,
            'stock_lines' => (int) $totals->stock_lines,
            'stock_accuracy' => $totals->stock_lines ? round($totals->stock_matching / $totals->stock_lines * 100, 1) : null,
            'books_expected' => (int) $totals->books_expected,
            'books_seen' => (int) $totals->books_seen,
            'holding_rate' => $totals->books_expected ? round($totals->books_seen / $totals->books_expected * 100, 1) : null,
            'results' => collect(self::RESULTS)->map(fn ($label, $result) => $this->applyResult($this->filtered(), $result)->count())->all(),
        ];
    }

    private function location(): ScopedLocation
    {
        return ScopedLocation::for(Auth::user());
    }

    public function startVisit()
    {
        $this->validate(['schoolId' => 'required|integer'], [], ['schoolId' => 'school']);

        return redirect()->route('manual-tracking.visits.create', $this->schoolId);
    }

    protected function permissionMap(): array
    {
        return [
            'view' => 'manual-tracking.view',
            'actions' => [
                'startVisit' => 'manual-tracking.visits.record',
            ],
        ];
    }
}
