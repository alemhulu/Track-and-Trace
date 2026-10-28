<?php

namespace App\Http\Livewire\BookPackage;

use App\Http\Livewire\Concerns\AuthorizesPermissions;
use Livewire\Component;

class PackagesRejectingFrom extends Component
{
    use AuthorizesPermissions;

    public function render()
    {
        return view('livewire.book-package.packages-rejecting-from');
    }

         // Clear input variables 
         public function clearid(){
            
        }

    protected function permissionMap(): array
    {
        return ['view' => 'packages.view'];
    }
}
