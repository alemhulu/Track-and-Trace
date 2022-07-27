<?php

namespace App\Http\Livewire\Oganization;

use App\Models\Organization;
use Livewire\Component;

class ListOrganization extends Component
{
    public function mount()
    {
        $this->organizations = Organization::all();
    }

    public function render()
    {
        return view('livewire.oganization.list-organization');
    }
}
