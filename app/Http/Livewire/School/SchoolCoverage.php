<?php

namespace App\Http\Livewire\School;

use App\Http\Livewire\Concerns\AuthorizesPermissions;
use App\Http\Livewire\School\Concerns\LoadsSchool;
use App\Services\School\SchoolAllocationService;
use Livewire\Component;

class SchoolCoverage extends Component
{
    use LoadsSchool;
    use AuthorizesPermissions;

    protected $listeners = ['schoolRecordsChanged' => '$refresh'];

    public function render(SchoolAllocationService $allocations)
    {
        $rows = $allocations->forSchool($this->school(), (int) $this->year);

        return view('livewire.school.school-coverage', [
            'rows' => $rows,
            'totals' => $allocations->totals($rows),
        ]);
    }

    protected function permissionMap(): array
    {
        return ['view' => 'manual-tracking.schools.view'];
    }
}
