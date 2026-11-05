<?php

namespace App\Http\Livewire\School;

use App\Http\Livewire\Concerns\AuthorizesPermissions;
use App\Models\ManualTracking\ManualDistribution;
use App\Models\ManualTracking\ManualStockBalance;
use App\Models\Organization;
use App\Models\School\SchoolEnrolment;
use App\Models\School\Student;
use App\Models\School\StudentBookIssue;
use App\Services\School\SchoolAllocationService;
use App\Support\AcademicYear;
use App\Support\ScopedLocation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class SchoolIndex extends Component
{
    use WithPagination;
    use AuthorizesPermissions;

    public $year;
    public $search = '';
    public $recordes = 10;
    public $area = ['region_id' => '', 'zone_id' => '', 'woreda_id' => ''];
    // '' or one of STATES
    public $state = '';

    public const STATES = [
        'no_enrolment' => 'No enrolment recorded',
        'awaiting' => 'Shipment on the way',
        'never_delivered' => 'Nothing delivered yet',
        'with_stock' => 'Books on the shelf',
    ];

    protected $queryString = ['year', 'search' => ['except' => ''], 'state' => ['except' => '']];

    public function mount()
    {
        $this->year = (int) ($this->year ?: AcademicYear::current());
        $this->area = $this->location()->apply([]);
    }

    public function updatedArea($value, $key)
    {
        $this->area = $this->location()->apply($this->area, $key);
        $this->resetPage();
    }

    public function updatingState()
    {
        $this->resetPage();
    }

    public function updatingYear()
    {
        $this->resetPage();
    }

    public function setState(string $state): void
    {
        $this->state = $this->state === $state || ! isset(self::STATES[$state]) ? '' : $state;
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset('search', 'state');
        $this->area = $this->location()->apply([]);
        $this->resetPage();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingRecordes()
    {
        $this->resetPage();
    }

    public function render(SchoolAllocationService $allocations)
    {
        $schools = $this->filtered()
            ->when($this->state !== '', fn ($query) => $this->applyState($query, $this->state))
            ->with(['woreda:id,name', 'zone:id,name'])
            ->orderBy('name')
            ->paginate($this->recordes);

        $totals = $allocations->summaries($schools->getCollection(), (int) $this->year);

        return view('livewire.school.school-index', [
            'schools' => $schools,
            'totals' => $totals,
            'years' => AcademicYear::options(),
            'stats' => $this->stats(),
            'locationPicker' => $this->location()->picker($this->area, Organization::query()->whereRaw('1 = 0')),
            'filtersActive' => trim((string) $this->search) !== '' || $this->state !== ''
                || collect($this->area)->filter()->diffAssoc($this->location()->locked())->isNotEmpty(),
        ])->extends('main.manual-tracking.index');
    }

    /**
     * Schools the user can see in the chosen area, matching the search.
     */
    private function filtered(): Builder
    {
        return $this->location()->organizations($this->area)
            ->whereHas('organizationType', fn ($type) => $type->where('name', 'School'))
            ->when(trim((string) $this->search) !== '', fn ($query) => $query->where('name', 'like', '%' . trim($this->search) . '%'));
    }

    private function applyState(Builder $query, string $state): Builder
    {
        $year = (int) $this->year;

        return match ($state) {
            'no_enrolment' => $query->whereNotIn('id', SchoolEnrolment::query()->where('academic_year', $year)->select('organization_id'))
                ->whereNotIn('id', Student::query()->active()->where('academic_year', $year)->select('organization_id')),
            'awaiting' => $query->whereIn('id', ManualDistribution::query()->where('status', ManualDistribution::STATUS_DISPATCHED)->select('destination_organization_id')),
            'never_delivered' => $query->whereNotIn('id', ManualDistribution::query()->whereNotNull('destination_organization_id')
                ->where('status', '!=', ManualDistribution::STATUS_DISPATCHED)->select('destination_organization_id')),
            'with_stock' => $query->whereIn('id', ManualStockBalance::query()->where('on_hand', '>', 0)->select('organization_id')),
            default => $query,
        };
    }

    /**
     * Figures over every matching school, ignoring the state filter whose counts they show.
     */
    private function stats(): array
    {
        $year = (int) $this->year;
        $ids = fn () => $this->filtered()->select('organizations.id');
        $enrolment = SchoolEnrolment::query()->whereIn('organization_id', $ids())->where('academic_year', $year)
            ->selectRaw('COUNT(DISTINCT organization_id) as schools, COALESCE(SUM(male), 0) as male, COALESCE(SUM(female), 0) as female')
            ->first();
        $students = Student::query()->active()->whereIn('organization_id', $ids())->where('academic_year', $year)->count();
        $issued = StudentBookIssue::query()
            ->join('students', 'students.id', '=', 'student_book_issues.student_id')
            ->whereNull('student_book_issues.returned_at')
            ->whereIn('students.organization_id', $ids())
            ->where('students.academic_year', $year)
            ->count();
        $onHand = ManualStockBalance::query()->whereIn('organization_id', $ids())
            ->selectRaw("COALESCE(SUM(CASE WHEN edition = 'student' THEN on_hand ELSE 0 END), 0) as student")
            ->selectRaw("COALESCE(SUM(CASE WHEN edition = 'teacher' THEN on_hand ELSE 0 END), 0) as teacher")
            ->first();
        $states = collect(self::STATES)->map(fn ($label, $state) => $this->applyState($this->filtered(), $state)->count())->all();

        return [
            'schools' => $this->filtered()->count(),
            'enrolled_schools' => (int) ($enrolment->schools ?? 0),
            'enrolled' => (int) ($enrolment->male ?? 0) + (int) ($enrolment->female ?? 0),
            'female' => (int) ($enrolment->female ?? 0),
            'students' => $students,
            'issued' => $issued,
            'student_on_hand' => (int) ($onHand->student ?? 0),
            'teacher_on_hand' => (int) ($onHand->teacher ?? 0),
            'states' => $states,
        ];
    }

    private function location(): ScopedLocation
    {
        return ScopedLocation::for(Auth::user());
    }

    protected function permissionMap(): array
    {
        return ['view' => 'manual-tracking.schools.view'];
    }
}
