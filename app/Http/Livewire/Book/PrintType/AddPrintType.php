<?php

namespace App\Http\Livewire\Book\PrintType;

use Livewire\Component;

class AddPrintType extends Component
{
    public function mount()
    {
        $this->printTypes = array();
    }

    public function render()
    {
        return view('livewire.book.print-type.add-print-type');
    }
}
