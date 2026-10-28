<?php

namespace App\Http\Livewire\School;

use App\Http\Livewire\Concerns\AuthorizesPermissions;
use App\Models\Organization;
use App\Support\AcademicYear;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class SchoolShow extends Component
{
    use AuthorizesPermissions;

    public $schoolId;
    public $year;

    protected $queryString = ['year'];

    public function mount($school)
    {
        $this->schoolId = Organization::query()->accessibleBy(Auth::user())->findOrFail($school)->id;
        $this->year = (int) ($this->year ?: AcademicYear::current());
    }

    public function render()
    {
        return view('livewire.school.school-show', [
            'school' => Organization::query()->with(['woreda:id,name', 'zone:id,name', 'region:id,name'])->findOrFail($this->schoolId),
            'years' => AcademicYear::options(),
        ])->extends('main.manual-tracking.index');
    }

    protected function permissionMap(): array
    {
        return ['view' => 'manual-tracking.schools.view'];
    }
}
