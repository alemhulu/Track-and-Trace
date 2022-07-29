<?php

namespace App\Http\Livewire\Book\BookType;

use Livewire\Component;

class AddBookType extends Component
{
    public function mount()
    {
        $this->bookTypes = array();
    }
    
    public function render()
    {
        return view('livewire.book.book-type.add-book-type');
    }
}
