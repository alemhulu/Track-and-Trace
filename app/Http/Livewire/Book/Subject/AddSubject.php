<?php

namespace App\Http\Livewire\Book\Subject;

use Livewire\Component;

class AddSubject extends Component
{
    public function mount()
    {
        $this->subjects = array();
    }

    public function render()
    {
        return view('livewire.book.subject.add-subject');
    }
}
