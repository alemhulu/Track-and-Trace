<?php

namespace App\Http\Livewire\Location;

use App\Models\country;
use App\Models\region;
use App\Models\zone as zones;
use Livewire\Component;
use Livewire\WithPagination;

class Zone extends Component
{
    use WithPagination;

    public $country;
    public $country_id;
    public $region;
    public $region_id;
    public $zone;
    public $name;
    public $is_subcity;
    
    protected $rules = [
        'name' => 'required|min:3|max:20|unique:zones',
        'is_subcity' => 'required',
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
        $this->resetPage();
    }

    public function mount()
    {
        $this->countries = country::all();
        $this->regions = region::all();
        $this->is_subcity = 0;
    }


    public function render()
    {
        return view('livewire.location.zone', 
        ['zones'=>zones::when($this->country_id,function($q,$country_id){
            return $q->where('country_id',$this->country_id);
        })
        ->when($this->region_id,function($q,$region_id){
            return $q->where('region_id',$this->region_id);
        })
        ->when($this->column,function($q,$column){
            return $q->orderBy($this->column,$this->sortType);
        })->paginate($this->recordes)
      ]);
    }

    public function addZone()
    {
        $this->validate();
        zones::create([
            'country_id'=> $this->country_id,
            'region_id'=> $this->region_id,
            'name'=> $this->name,
            'is_subcity'=> $this->is_subcity
        ]);

        $this->emit('zoneAdded');
        $this->alertSuccess();
        $this->resetFields();
        $this->column='created_at';
        $this->sortType='desc';
        $this->resetPage();
        $this->emit('newZone');
    }

    public function editZone($id){
        $this->emit('editZone',$id);
    }

    public function deleteZone(zones $zone){
        if($zone->woredas->count()){
            return $this->deleteError('Zone cannot be deleted, it has related data!');
        }

        $zone->delete();
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
        'updatedCountry' => 'mount',
        'updatedRegion' => 'mount',
        'updatedZone'
        ];

    public function updatedZone(){
        $this->column='updated_at';
        $this->sortType='desc';
        $this->resetPage();
    }

    public function alertSuccess()
    {
        $this->dispatchBrowserEvent(
            'alert',
            ['type' => 'success',  'message' => 'Zone Created Successfully!']
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
            ['type' => 'success',  'message' => 'Zone Deleted Successfully!']
        );
    }

    public function resetFields()
    {
        $this->name="";
        $this->code="";
    }
}
