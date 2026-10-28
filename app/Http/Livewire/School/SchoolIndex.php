<?php

namespace App\Http\Livewire\School;

use App\Models\Organization;
use App\Services\School\SchoolAllocationService;
use App\Support\AcademicYear;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class SchoolIndex extends Component
{
    use WithPagination;

    public $year;
    public $search = '';

    protected $queryString = ['year', 'search' => ['except' => '']];

    public function mount()
    {
        $this->year = (int) ($this->year ?: AcademicYear::current());
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render(SchoolAllocationService $allocations)
    {
        $schools = Organization::query()
            ->accessibleBy(Auth::user())
            ->whereHas('organizationType', fn ($type) => $type->where('name', 'School'))
            ->with(['woreda:id,name', 'zone:id,name'])
            ->when($this->search, fn ($query) => $query->where('name', 'like', '%' . $this->search . '%'))
            ->orderBy('name')
            ->paginate(15);

        $totals = $allocations->summaries($schools->getCollection(), (int) $this->year);

        return view('livewire.school.school-index', [
            'schools' => $schools,
            'totals' => $totals,
            'years' => AcademicYear::options(),
        ])->extends('main.manual-tracking.index');
    }
}
