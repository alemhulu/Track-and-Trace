<?php

namespace App\Http\Livewire\Location;

use Livewire\Component;
use App\Models\country;
use App\Models\region as regions;
use Livewire\WithPagination;

class Region extends Component
{

    use WithPagination;

    public $country;
    public $country_id;
    public $name;
    public $is_city;

    protected $rules = [
        'name' => 'required|min:3|max:20|unique:regions',
        'is_city' => 'required',
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
        $this->resetPage();
    }

    public function mount()
    {
        $this->countries = country::all();
        $this->is_city = 0;
    }
    
    public function render()
    {
        return view('livewire.location.region', 
        ['regions'=>regions::when($this->country_id,function($q,$country_id){
            return $q->where('country_id',$this->country_id);
        })
        ->when($this->column,function($q,$column){
            return $q->orderBy($this->column,$this->sortType);
        })->paginate($this->recordes)
    ])->extends('main.location.index');
    }

    public function addRegion()
    {
        $this->validate();
        regions::create([
            'country_id'=> $this->country_id,
            'name'=> $this->name,
            'is_city'=> $this->is_city
        ]);

        $this->emit('regionAdded');
        $this->alertSuccess();
        $this->resetFields();
        $this->column='created_at';
        $this->sortType='desc';
        $this->resetPage();
        $this->emit('newRegion');
    }

    public function editRegion($id){
        $this->emit('editRegion',$id);
    }

    public function deleteRegion(regions $region){
        if($region->zones->count()){
            return $this->deleteError('Region cannot be deleted, it has related data!');
        }

        $region->delete();
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

    protected $listeners = ['countryAdded' => 'mount','updatedRegion'];
   
    public function updatedRegion(){
        $this->column='updated_at';
        $this->sortType='desc';
        $this->resetPage();
    }

    public function alertSuccess()
    {
        $this->dispatchBrowserEvent(
            'alert',
            ['type' => 'success',  'message' => 'Region Created Successfully!']
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
            ['type' => 'success',  'message' => 'Region Deleted Successfully!']
        );
    }

    public function resetFields()
    {
        $this->name="";
        $this->code="";
    }
}
