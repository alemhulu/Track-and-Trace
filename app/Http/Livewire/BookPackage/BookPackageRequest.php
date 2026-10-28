<?php

namespace App\Http\Livewire\BookPackage;

use App\Http\Livewire\Concerns\AuthorizesPermissions;
use Livewire\Component;

class BookPackageRequest extends Component
{
    use AuthorizesPermissions;


    public function viewRequestInfo($id)
    {
        return redirect()->route('packages.request.info');
    }
    
    public function render()
    {
        return view('livewire.book-package.book-package-request')->extends('main.book-package.index');
    }

    protected function permissionMap(): array
    {
        return ['view' => 'packages.request'];
    }
}
