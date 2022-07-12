<?php

namespace App\Http\Livewire\Location\Modal;

use App\Models\country;
use App\Models\region;
use App\Models\woreda;
use App\Models\zone;
use Livewire\Component;

class EditWoreda extends Component
{
    public $countries;
    public $regions;
    public $zones;
    public woreda $woreda;

    function rules(){
        return  [
            'woreda.name'=>'required|unique:woredas,name,'.$this->woreda->id,
            'woreda.zone_id' => 'nullable',
            'woreda.region_id' => 'required',
            'woreda.country_id' => 'required'
        ];
    }
    
    protected $listeners=[
        'editWoreda'
    ];
    
        public function editWoreda(woreda $woreda){
            $this->woreda=$woreda;
        }
    
        public function mount(woreda $woreda)
        {
            $this->woreda=$woreda;
            $this->countries = country::orderBy('name')->get();
            $this->regions = region::orderBy('name')->get();
            $this->zones = zone::orderBy('name')->get();
        }
    
        public function clearid(){
            unset($this->woreda->name);
        }
    
        public function submit(){
            $this->validate();
            $this->woreda->save();
            $this->alertSuccess();
            $this->emit('updatedWoreda');
        }
    
        public function alertSuccess()
        {
            $this->dispatchBrowserEvent(
                'alert',
                ['type' => 'success',  'message' => 'Woreda Updated Successfully!']
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
        return view('livewire.location.modal.edit-woreda');
    }
}
