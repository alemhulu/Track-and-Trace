<?php

namespace App\Http\Livewire\Location\Modal;

use App\Models\Country;
use App\Models\Region;
use Livewire\Component;
use App\Models\zone;
class EditZone extends Component
{
    public $countries;
    public $regions;
    public zone $zone;

    function rules(){
        return  [
             'zone.name'=>'required|unique:zones,name,'.$this->zone->id,
             'zone.is_subcity' => 'required',
             'zone.region_id' => 'required',
             'zone.country_id' => 'required'
         ];
     }

     protected $listeners=[
        'editZone'
    ];

    public function editZone(zone $zone){
        $this->zone=$zone;
    }
     
    public function mount(zone $zone)
    {
        $this->zone=$zone;
        $this->countries = country::orderBy('name')->get();
        $this->regions = region::orderBy('name')->get();
    }

    public function updatedCountryId()
    {
        if($this->country_id==""){
            $this->regions=region::orderBy('name')->get();
         }else{
             $this->regions = region::where('country_id', $this->country_id)->get();
         }
        $this->resetPage();
    }

    public function updatedRegionId()
    {
        $this->resetPage();
    }

    public function clearid(){
        unset($this->zone->name);
        unset($this->zone->is_subcity);
    }

    public function submit(){
        $this->validate();
        $this->zone->save();
        $this->alertSuccess();
        $this->emit('updatedZone');
    }

    public function alertSuccess()
    {
        $this->dispatchBrowserEvent(
            'alert',
            ['type' => 'success',  'message' => 'Zone Updated Successfully!']
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
        return view('livewire.location.modal.edit-zone');
    }
}
