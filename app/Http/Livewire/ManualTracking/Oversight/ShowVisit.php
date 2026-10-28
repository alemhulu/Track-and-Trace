<?php

namespace App\Http\Livewire\ManualTracking\Oversight;

use App\Models\Oversight\VerificationVisit;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ShowVisit extends Component
{
    public $visitId;

    public function mount($visit)
    {
        $this->visitId = VerificationVisit::query()->accessibleBy(Auth::user())->findOrFail($visit)->id;
    }

    public function render()
    {
        $visit = VerificationVisit::query()
            ->with(['school:id,name', 'visitor:id,name', 'items.book:id,title', 'items.student:id,name,student_no,section'])
            ->findOrFail($this->visitId);

        return view('livewire.manual-tracking.oversight.show-visit', [
            'visit' => $visit,
            'stockItems' => $visit->items->where('kind', 'stock')->values(),
            'studentItems' => $visit->items->where('kind', 'student')->groupBy('student_id'),
        ])->extends('main.manual-tracking.index');
    }
}
