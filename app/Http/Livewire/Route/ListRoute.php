<?php

namespace App\Http\Livewire\Route;

use Livewire\Component;

class ListRoute extends Component
{
    public function render()
    {
        return view('livewire.route.list-route')->extends('main.route.index');
    }
}
