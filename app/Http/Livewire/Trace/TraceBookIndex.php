<?php

namespace App\Http\Livewire\Trace;

use App\Models\Grade;
use App\Models\Subject;
use Livewire\Component;

class TraceBookIndex extends Component
{
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
}
