<?php

namespace App\Http\Livewire\Oganization;

use App\Models\Organization;
use Livewire\Component;
use Livewire\WithPagination;

class ListOrganization extends Component
{
    use WithPagination;

    public $search='';
    public $recordes=5;
    public $column='created_at';
    public $sortType='asc';
    protected $columns= ['name'];

   //  Search function
    public function updatedSearch(){
        $this->column='name';
        $this->sortType='asc';
        $this->resetPage();
    }

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

    // Reset pagination on every variable updated
    public function updated(){
        $this->resetPage();
    }


    public function render()
    {
        return view('livewire.oganization.list-organization',
        ['organizations'=>Organization::
            search($this->columns,$this->search)
            ->when($this->column,function($q,$column){
                return $q->orderBy($this->column,$this->sortType);
            })->paginate($this->recordes)
    ])->extends('main.organization.index');
    }

    public $deleteId ="";

    public function deleteId($id){
        $this->deleteId = $id;
    }

    // Delete the available data
    public function deleteOrganization(Organization $Organization){
        if($Organization->users->count()){
            return $this->deleteError('Organization cannot be deleted, it has related data!');
        }
        $Organization->delete();
        $this->alertDelete();
        $this->resetPage();
    }

        // Clear input variables 
        public function clearid(){
            $this->name="";
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
            'alert', ['type' => 'success',  'message' => 'Organization Added Successfuly']
        );
    }

     // Alert Delete Notification
    public function alertDelete()
    {
        $this->dispatchBrowserEvent(
            'alert',
            ['type' => 'success',  'message' => 'Organization Deleted Successfully!']
        );
    }
   
    // Reset Error 
    public function hydrate()
    {
        $this->resetErrorBag();
        $this->resetValidation();
    }

}
