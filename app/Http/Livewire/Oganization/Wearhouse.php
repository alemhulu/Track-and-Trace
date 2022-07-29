<?php

namespace App\Http\Livewire\Oganization;

use Livewire\Component;

class Wearhouse extends Component
{
    public function mount()
    {
        $this->wearhouses = [];
    }

    public function render()
    {
        return view('livewire.oganization.wearhouse');
    }
}
