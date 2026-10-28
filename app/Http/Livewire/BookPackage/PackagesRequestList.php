<?php

namespace App\Http\Livewire\BookPackage;

use App\Http\Livewire\Concerns\AuthorizesPermissions;
use Livewire\Component;

class PackagesRequestList extends Component
{
    use AuthorizesPermissions;

    public function render()
    {
        return view('livewire.book-package.packages-request-list');
    }

    protected function permissionMap(): array
    {
        return ['view' => 'packages.view'];
    }
}
