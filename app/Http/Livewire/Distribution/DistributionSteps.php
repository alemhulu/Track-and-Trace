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
        $this->steps = Steps::all();
    }

    public function addStep()
    {
        $this->steps->push( new Steps() );
    }

    public function deleteStep($index)
    {
        $route = $this->steps[$index];
        $this->steps->forget($index);

        $route->delete();
    }    

    public function save()
    {
        $this->validate();
    }
}
