<?php

namespace App\Http\Livewire\Distribution;

use Livewire\Component;
use App\Models\DistributionSteps as Steps;

class DistributionSteps extends Component
{

    public $steps;

    protected $rules = [
        'name' => 'required',
        'description' => 'required',
        'is_active' => 'nullable',
        'steps.*.route_id' => 'required',
        'steps.*.distribution_id' => 'required',
    ];

    public function render()
    {
        return view('livewire.distribution.distribution-steps');
    }

    public function mount()
    {
        $this->steps = [];
    }


    public function save()
    {
        $this->validate();
    }
}
