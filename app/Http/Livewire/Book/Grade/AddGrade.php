<?php

namespace App\Http\Livewire\Book\Grade;

use Livewire\Component;

class AddGrade extends Component
{
    public function mount()
    {
        $this->grades = array();
    }
    
    public function render()
    {
        return view('livewire.book.grade.add-grade');
    }
}
