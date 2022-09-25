<?php

namespace App\Http\Livewire\Location;

use Livewire\Component;
use App\Models\country as ModelsCountry;
use Livewire\WithPagination;

class Country extends Component
{
    use WithPagination;

    // Input Variables
    public $name;
    public $code;

    // Validation rules
    protected $rules = [
        'name' => 'required|min:2|max:50|unique:countries'
    ];

    // Table variables
    public $recordes=5;
    public $column='name';
    public $sortType='asc';

    // sort function
    public function sort($value){
    if($this->column==$value && $this->sortType=='asc'){
        $this->sortType='desc';
    }else{
        $this->column=$value;
        $this->sortType='asc';
    }
    $this->resetPage();
    }

     // Search variables
    public $search='';
    public $attributes;

     // Search function 
    public function updatedSearch(){
        $this->column='name';
        $this->sortType='asc';
        $this->resetPage();
    }

    // Reset pagination on every variable updated
    public function updated(){
        $this->resetPage();
    }

    // Create new data
    public function addCountry()
    {
        $this->validate();
        ModelsCountry::create([ 'name' => $this->name ]);
        $this->column='created_at';
        $this->sortType='desc';
        $this->resetPage();
        $this->emit('countryAdded');
        $this->alertSuccess();
        $this->resetFields();
    }

    // edit data
    public function editCountry($id){
        $this->emit('editCountry', $id);
    }

    public $deleteId ="";

    public function deleteId($id){
        $this->deleteId = $id;
    }
    // Delete the available data
    public function deleteCountry(ModelsCountry $country){
        if($country->regions->count()){
            return $this->deleteError('Country cannot be deleted, it has related data!');
        }
        $country->delete();
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

    // Update call Listener
    protected $listeners=[
        'updatedCountry'
    ];

    public function updatedCountry(){
        $this->column='updated_at';
        $this->sortType='desc';
        $this->resetPage();
    }

    // Alert Error Notification
    public function alertError($name)
    {
        $this->dispatchBrowserEvent(
            'alert', ['type' => 'error',  'message' => $name.' Required!']
        );
    }

    // Alert Success Notification
    public function alertSuccess()
    {
        $this->dispatchBrowserEvent(
            'alert', ['type' => 'success',  'message' => 'Country Added Successfuly']
        );
    }

     // Alert Delete Notification
    public function alertDelete()
    {
        $this->dispatchBrowserEvent(
            'alert',
            ['type' => 'success',  'message' => 'Country Deleted Successfully!']
        );
    }
   
    // Reset Error 
    public function hydrate()
    {
        $this->resetErrorBag();
        $this->resetValidation();
    }

    // Render
    public function render()
    {
        return view('livewire.location.country',
        ['countries'=>ModelsCountry::when($this->column,function($q,$column){
                return $q->orderBy($this->column,$this->sortType);
            })->paginate($this->recordes)
    ])->extends('main.location.index');
    }

    // Clear input variables 
    public function clearid(){
        $this->name="";
    }
    
    public function resetFields()
    {
        $this->name="";
    }
}
