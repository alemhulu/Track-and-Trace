<?php

namespace App\Http\Livewire\Distribution;

use App\Models\DistributionSteps;
use App\Models\User;
use Livewire\Component;

class AddDistribution extends Component
{
    public $steps;
    public $name;
    public $description;
    public $is_active;

    public $valueT = false;

    protected $rules = [
        'name' => 'required',
        'description' => 'required',
        'is_active' => 'nullable',
        'steps.*.route_id' => 'required',
        'steps.*.distribution_id' => 'required',
    ];

    public function render()
    {
        return view('livewire.distribution.add-distribution');
    }

    public function mount()
    {
        $this->steps = DistributionSteps::all();
    }

    public function addStep()
    {
        $this->steps->push( new DistributionSteps() );
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

    public function alertError($name)
    {
        $this->dispatchBrowserEvent(
            'alert',
            ['type' => 'error',  'message' => $name.' Required!']
        );
    }
}
