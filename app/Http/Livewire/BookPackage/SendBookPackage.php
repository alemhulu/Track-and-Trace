<?php

namespace App\Http\Livewire\BookPackage;

use App\Http\Livewire\Concerns\AuthorizesPermissions;
use Livewire\Component;

class SendBookPackage extends Component
{
    use AuthorizesPermissions;

    public function render()
    {
        return view('livewire.book-package.send-book-package')->extends('main.book-package.index');
    }

    protected function permissionMap(): array
    {
        return ['view' => 'packages.send'];
    }
}
