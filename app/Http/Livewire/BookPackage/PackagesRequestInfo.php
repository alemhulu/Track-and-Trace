<?php

namespace App\Http\Livewire\BookPackage;

use App\Http\Livewire\Concerns\AuthorizesPermissions;
use Livewire\Component;

class PackagesRequestInfo extends Component
{
    use AuthorizesPermissions;



    public function render()
    {
        return view('livewire.book-package.packages-request-info')->extends('main.book-package.index');
    }

    // Clear input variables 
    public function clearid(){
    
    }

    protected function permissionMap(): array
    {
        return ['view' => 'packages.view'];
    }
}
