<?php

namespace App\Http\Livewire\BookPackage;

use Livewire\Component;

class AvialableBookPackage extends Component
{
    public function render()
    {
        return view('livewire.book-package.avialable-book-package')->extends('main.book-package.index');
    }
}
