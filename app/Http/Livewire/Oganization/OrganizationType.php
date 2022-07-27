<?php

namespace App\Http\Livewire\Oganization;

use Livewire\Component;

class OrganizationType extends Component
{
    

    public function mount()
    {
        $this->types = OrganizationType::all();
    }
    public function render()
    {
        return view('livewire.oganization.organization-type');
    }
}
