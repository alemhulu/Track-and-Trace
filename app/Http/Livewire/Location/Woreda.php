<?php

namespace App\Http\Livewire\Location;

use App\Models\country;
use App\Models\region;
use App\Models\woreda as woredas;
use App\Models\zone;
use Livewire\Component;
use Livewire\WithPagination;

class Woreda extends Component
{
    use WithPagination;

    public $country;
    public $country_id;
    public $region;
    public $regions = [];
    public $region_id;
    public $zone;
    public $zone_id;
    public $name;

    protected $rules = [
        'name' => 'required|min:2|max:20|unique:woredas,name,zone_id,region_id',
        'zone_id' => 'nullable',
        'region_id' => 'required',
        'country_id' => 'required'
    ];

    public function hydrate()
    {
        $this->resetErrorBag();
        $this->resetValidation();
    }

    public $recordes=5;
    public $column='name';
    public $sortType='asc';

    public function sort($value){
        if($this->column==$value && $this->sortType=='asc'){
            $this->sortType='desc';
        }else{
            $this->column=$value;
            $this->sortType='asc';
        }
        $this->resetPage();
    }
  
    public $search='';
    // public $attributes=[
    //     'name', 'code'
    // ];

    public function updatedSearch(){
        $this->column='name';
        $this->sortType='asc';
        $this->resetPage();
    }
   
    public function updated(){
        $this->resetPage();
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
        if($this->region_id == ""){
            $this->zones=zone::orderBy('name')->get();
         }else{
             $this->zones = zone::where('region_id', $this->region_id)->get();
         }
        $this->resetPage();
    }

    public function mount()
    {
        $this->countries = country::all();
        $this->regions = region::all();
        $this->zones = zone::all();
    }

    public function render()
    {
        return view('livewire.location.woreda', 
        ['woredas'=>woredas::when($this->country_id,function($q,$country_id){
            return $q->where('country_id',$this->country_id);
        })
        ->when($this->region_id,function($q,$region_id){
            return $q->where('region_id',$this->region_id);
        })
        ->when($this->zone_id,function($q,$zone_id){
            return $q->where('zone_id',$this->zone_id);
        })
        ->when($this->column,function($q,$column){
            return $q->orderBy($this->column,$this->sortType);
        })->paginate($this->recordes)
        ])->extends('main.location.index');
    }

    public function addWoreda()
    {
        $this->validate();
        woredas::create([
            'country_id'=> $this->country_id,
            'region_id'=> $this->region_id,
            'zone_id'=> $this->zone_id,
            'name'=> $this->name
        ]);

        $this->emit('woredaAdded');
        $this->alertSuccess();
        $this->resetFields();
        $this->column='created_at';
        $this->sortType='desc';
        $this->resetPage();
        $this->emit('newWoreda');
    }

    public function editWoreda($id){
        $this->emit('editWoreda',$id);
    }

    public $deleteId ="";

    public function deleteId($id){
        $this->deleteId = $id;
    }
    
    public function deleteWoreda(woredas $woreda){
        $woreda->delete();
        $this->alertDelete();
        $this->resetPage();
    }


    protected $listeners = [
        'countryAdded' => 'mount',
        'regionAdded' => 'mount',
        'updatedCountry' => 'mount',
        'updatedRegion' => 'mount',
        'updatedZone' => 'mount',
        'updatedWoreda'
        ];

        public function updatedWoreda(){
            $this->column='updated_at';
            $this->sortType='desc';
            $this->resetPage();
        }

        public function alertSuccess()
        {
            $this->dispatchBrowserEvent(
                'alert',
                ['type' => 'success',  'message' => 'Woreda Created Successfully!']
            );
        }

        public function alertError($name)
        {
            $this->dispatchBrowserEvent(
                'alert',
                ['type' => 'error',  'message' => $name.' Required!']
            );
        }

        public function alertDelete()
        {
            $this->dispatchBrowserEvent(
                'alert',
                ['type' => 'success',  'message' => 'Woreda Deleted Successfully!']
            );
        }

        public function resetFields()
    {
        $this->name="";
        $this->zone_id="";
    }

        // Clear input variables 
        public function clearid(){
            $this->name="";
        }
}
