<?php

namespace App\Http\Livewire;

use App\Models\Country;
use App\Models\Region;
use App\Models\Woreda;
use App\Models\Zone;
use Livewire\Component;

class CountLocation extends Component
{
    public $countries,$regions,$zones,$woredas;
    public function render()
    {
        return view('livewire.count-location');
    }
    public function mount()
    {
        $this->countries=Country::all()->count();
        $this->regions=Region::all()->count();
        $this->zones=Zone::all()->count();
        $this->woredas=Woreda::all()->count();
    }
}
