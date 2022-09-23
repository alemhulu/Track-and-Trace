<?php

namespace App\Http\Livewire\BookPackage;

use Livewire\Component;

class SendBookPackage extends Component
{
    public function render()
    {
        return view('livewire.book-package.send-book-package')->extends('main.book-package.index');
    }
}
