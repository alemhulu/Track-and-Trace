<?php

namespace App\Http\Livewire\Oganization;
use App\Models\OrganizationType as ModelsOrganizationType;

use Livewire\Component;
use Livewire\WithPagination;

class OrganizationType extends Component
{
    
    use WithPagination;

    // Input Variables
    public $name;
    public $code;
    public $description;

    // Validation rules
    protected $rules = [
        'name' => 'required|min:2|max:50|unique:organization_types',
        'code' =>'nullable|min:2|max:5|unique:organization_types'
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
    public function addOrganizationType()
    {
        $this->validate();
        ModelsOrganizationType::create([ 'name' => $this->name,  'code'=> $this->code,
        'description'=> $this->description ]);
        $this->column='created_at';
        $this->sortType='desc';
        $this->resetPage();
        $this->emit('OrganizationTypeAdded');
        $this->alertSuccess();
        $this->resetFields();
    }

    // edit data
    public function editType($id){
        $this->emit('editType', $id);
    }

    // Delete the available data
    public function deleteOrganizationType(ModelsOrganizationType $OrganizationType){
        if($OrganizationType->regions->count()){
            return $this->deleteError('OrganizationType cannot be deleted, it has related data!');
        }
        $OrganizationType->delete();
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
        'updatedOrganizationType'
    ];

    public function updatedOrganizationType(){
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
            'alert', ['type' => 'success',  'message' => 'OrganizationType Added Successfuly']
        );
    }

     // Alert Delete Notification
    public function alertDelete()
    {
        $this->dispatchBrowserEvent(
            'alert',
            ['type' => 'success',  'message' => 'OrganizationType Deleted Successfully!']
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
        return view('livewire.Oganization.Organization-Type',
        ['types'=>ModelsOrganizationType::when($this->column,function($q,$column){
                return $q->orderBy($this->column,$this->sortType);
            })->paginate($this->recordes)
    ])->extends('main.organization.index');
    }

    // Clear input variables 
    public function clearid()
    {
        $this->name="";
    }
    
//     public function resetFields()
//     {
//         $this->name="";
//     }
//     public function mount()
//     {
//         $this->types = OrganizationType::all();
//     }
//     public function render()
//     {
//         return view('livewire.oganization.organization-type');
//     }

}
