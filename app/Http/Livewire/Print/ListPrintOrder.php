<?php

namespace App\Http\Livewire\Print;

use App\Models\PrintOrder;
use Livewire\Component;

use Livewire\WithPagination;
class ListPrintOrder extends Component
{
    use WithPagination;
    //variables
    public $printOrders=[];
    public $column, $recordes, $sortType;

       // Search variables
       public $search='';
       public $attributes;
   
        // Search function 
       public function updatedSearch(){
           $this->column='order_organization_id';
           $this->sortType='asc';
           $this->resetPage();
       }
    public function mount()
    {
        $this->printOrders=PrintOrder::all();
        $this->recordes=5;
        $this->column='';
    }
    public function render()
    {
        return view('livewire.print.list-print-order',
        ['orders'=>PrintOrder::
        when($this->column,function($q,$column){
            return $q->orderBy($this->column,$this->sortType);
        })->paginate($this->recordes)

    ])->extends('main.print-order.index');
    }

       // Reset pagination on every variable updated
       public function updated(){
        $this->resetPage();
    }
}
