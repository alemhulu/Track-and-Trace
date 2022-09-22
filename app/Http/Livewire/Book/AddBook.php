<?php

namespace App\Http\Livewire\Book;

use Livewire\Component;

class AddBook extends Component
{
    public function render()
    {
        return view('livewire.book.add-book')->extends('main.book.index');
    }
}
