<?php

namespace App\Http\Livewire\BookPackage;

use Livewire\Component;

class BookPackageRequest extends Component
{

    public function viewRequestInfo($id)
    {
        return redirect()->route('packages.request.info');
    }
    
    public function render()
    {
        return view('livewire.book-package.book-package-request')->extends('main.book-package.index');
    }
}
