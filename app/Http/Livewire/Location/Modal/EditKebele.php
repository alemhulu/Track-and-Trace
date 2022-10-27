<?php

namespace App\Http\Livewire\Location\Modal;

use App\Models\Country;
use App\Models\Kebele;
use App\Models\Region;
use App\Models\Woreda;
use App\Models\Zone;
use Livewire\Component;

class EditKebele extends Component
{
    public $countries;
    public $regions;
    public $zones;
    public $woredas;

    public Kebele $kebele;

    function rules(){
        return  [
            'kebele.name'=>'required|unique:kebeles,name,'.$this->kebele->id,
            'kebele.woreda_id' => 'nullable',
            'kebele.zone_id' => 'nullable',
            'kebele.region_id' => 'required',
            'kebele.country_id' => 'required'
        ];
    }

    protected $listeners=[
        'editKebele'
    ];

    public function editKebele(Kebele $kebele){
        $this->kebele=$kebele;
    }

    public function mount(Kebele $kebele)
        {
            $this->kebele=$kebele;
            $this->countries = country::orderBy('name')->get();
            $this->regions = Region::orderBy('name')->get();
            $this->zones = Zone::orderBy('name')->get();
            $this->woredas = woreda::orderBy('name')->get();
        }

    public function clearid(){
        unset($this->kebele->name);
    }

    public function submit(){
        $this->validate();
        $this->kebele->save();
        $this->alertSuccess();
        $this->emit('updatedKebele');
    }

    public function alertSuccess()
        {
            $this->dispatchBrowserEvent(
                'alert',
                ['type' => 'success',  'message' => 'Kebele Updated Successfully!']
            );
        }

    public function alertError($name)
    {
        $this->dispatchBrowserEvent(
            'alert',
            ['type' => 'error',  'message' => $name.' Required!']
        );
    }

    public function render()
    {
        return view('livewire.location.modal.edit-kebele');
    }
}
