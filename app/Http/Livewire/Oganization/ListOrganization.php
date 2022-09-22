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
    public $column='name';
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
    ]);
    }
}
