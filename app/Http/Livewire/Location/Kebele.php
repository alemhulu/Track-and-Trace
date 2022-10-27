<?php

namespace App\Http\Livewire\Location;

use App\Models\Country;
use App\Models\Kebele as ModelsKebele;
use App\Models\Region;
use App\Models\Woreda as woredas;
use App\Models\Zone;
use Livewire\Component;
use Livewire\WithPagination;

class Kebele extends Component
{
    use WithPagination;

    public $country;
    public $country_id;
    public $region;
    public $regions = [];
    public $region_id;
    public $zone;
    public $zone_id;
    public $woreda;
    public $woreda_id;
    public $name;

    protected $rules = [
        'name' => 'required|min:2|max:20|unique:woredas,name,zone_id,region_id',
        'woreda_id' => 'nullable',
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

    public function updatedZoneId()
    {
        if($this->zone_id == ""){
            $this->woredas=woredas::orderBy('name')->get();
         }else{
             $this->woredas = woredas::where('zone_id', $this->zone_id)->get();
         }
        $this->resetPage();
    }

    public function mount()
    {
        $this->countries = country::all();
        $this->regions = region::all();
        $this->zones = zone::all();
        $this->woredas = woredas::all();
    }

    public function render()
    {
        return view('livewire.location.kebele', 
        ['kebeles'=>ModelsKebele::when($this->country_id,function($q,$country_id){
            return $q->where('country_id',$this->country_id);
        })
        ->when($this->region_id,function($q,$region_id){
            return $q->where('region_id',$this->region_id);
        })
        ->when($this->zone_id,function($q,$zone_id){
            return $q->where('zone_id',$this->zone_id);
        })
        ->when($this->woreda_id,function($q,$woreda_id){
            return $q->where('woreda_id',$this->woreda_id);
        })
        ->when($this->column,function($q,$column){
            return $q->orderBy($this->column,$this->sortType);
        })->paginate($this->recordes)
        ])->extends('main.location.index');
    }

    public function addKebele()
    {
        $this->validate();
        ModelsKebele::create([
            'country_id'=> $this->country_id,
            'region_id'=> $this->region_id,
            'zone_id'=> $this->zone_id,
            'woreda_id'=> $this->woreda_id,
            'name'=> $this->name
        ]);

        $this->emit('kebeleAdded');
        $this->alertSuccess();
        $this->resetFields();
        $this->column='created_at';
        $this->sortType='desc';
        $this->resetPage();
        $this->emit('newKebele');
    }

    public $deleteId ="";

    public function deleteId($id){
        $this->deleteId = $id;
    }
    
    public function editKebele($id){
        $this->emit('editKebele',$id);
    }

    public function deleteKebele(ModelsKebele $kebele){
        $kebele->delete();
        $this->alertDelete();
        $this->resetPage();
    }

    public function deleteError($name)
    {
        $this->dispatchBrowserEvent(
            'alert',
            ['type' => 'error',  'message' => $name]
        );
    }

    protected $listeners = [
        'countryAdded' => 'mount',
        'regionAdded' => 'mount',
        'zoneAdded' => 'mount',
        'woredaAdded' => 'mount',
        'updatedCountry' => 'mount',
        'updatedRegion' => 'mount',
        'updatedZone' => 'mount',
        'updatedWoreda' => 'mount',
        'updatedKebele'
        ];

        public function updatedKebele(){
            $this->column='updated_at';
            $this->sortType='desc';
            $this->resetPage();
        }

        public function alertSuccess()
        {
            $this->dispatchBrowserEvent(
                'alert',
                ['type' => 'success',  'message' => 'Kebele Created Successfully!']
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
                ['type' => 'success',  'message' => 'Kebele Deleted Successfully!']
            );
        }

        public function resetFields()
    {
        $this->name="";
        $this->woreda_id="";
    }

}
