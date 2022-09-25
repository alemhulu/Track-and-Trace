<?php

namespace App\Http\Livewire\BookPackage;

use Livewire\Component;

class PackagesRequestInfo extends Component
{


    public function render()
    {
        return view('livewire.book-package.packages-request-info')->extends('main.book-package.index');
    }

    // Clear input variables 
    public function clearid(){
    
    }

}
