<?php

namespace App\Http\Livewire\ManualTracking\Oversight;

use App\Models\Organization;
use App\Models\Oversight\VerificationVisit;
use App\Support\AcademicYear;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class VisitIndex extends Component
{
    use WithPagination;

    public $year;
    public $schoolId = '';

    public function mount()
    {
        $this->year = AcademicYear::current();
    }

    public function updatingYear()
    {
        $this->resetPage();
    }

    public function render()
    {
        $actor = Auth::user();

        return view('livewire.manual-tracking.oversight.visit-index', [
            'visits' => VerificationVisit::query()
                ->accessibleBy($actor)
                ->where('academic_year', $this->year)
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
        ])->extends('main.manual-tracking.index');
    }

    public function startVisit()
    {
        $this->validate(['schoolId' => 'required|integer'], [], ['schoolId' => 'school']);

        return redirect()->route('manual-tracking.visits.create', $this->schoolId);
    }
}
