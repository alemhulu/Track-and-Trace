<?php

namespace App\Http\Livewire\Trace;

use App\Http\Livewire\Concerns\AuthorizesPermissions;
use App\Models\Grade;
use App\Models\Subject;
use Livewire\Component;

class TraceBookIndex extends Component
{
    use AuthorizesPermissions;

    public $grade_id = '';
    public $subject_id = '';
    public $grades = [];
    public $subjects = [];

    public function mount()
    {
        $this->grades = Grade::query()->orderBy('name')->get();
        $this->subjects = Subject::query()->orderBy('name')->get();
    }

    public function render()
    {
        return view('livewire.trace.trace-book-index');
    }

    protected function permissionMap(): array
    {
        return ['view' => 'trace.view'];
    }
}
