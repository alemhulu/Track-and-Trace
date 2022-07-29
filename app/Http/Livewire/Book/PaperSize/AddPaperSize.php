<?php

namespace App\Http\Livewire\Book\PaperSize;

use Livewire\Component;

class AddPaperSize extends Component
{

    public function mount()
    {
        $this->paperSizes = array();
    }

    public function render()
    {
        return view('livewire.book.paper-size.add-paper-size');
    }
}
