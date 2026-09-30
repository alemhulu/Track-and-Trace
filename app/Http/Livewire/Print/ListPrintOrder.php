<?php

namespace App\Http\Livewire\Print;

use App\Models\PrintOrder;
use Illuminate\Support\Facades\Gate;
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
        $actor = auth()->user();
        $this->printOrders = PrintOrder::query()
            ->when($actor, function ($query) use ($actor) {
                $query->accessibleBy($actor);
            })
            ->get();
        $this->recordes=5;
        $this->column='';
    }
    public function render()
    {
        $actor = auth()->user();
        if ($actor && Gate::denies('viewAny', PrintOrder::class)) {
            abort(403, 'You are not authorized to view print orders.');
        }

        return view('livewire.print.list-print-order', [
            'orders' => PrintOrder::query()
                ->when($actor, function ($query) use ($actor) {
                    $query->accessibleBy($actor);
                })
                ->when($this->column, function ($q, $column) {
                    return $q->orderBy($this->column, $this->sortType);
                })
                ->paginate($this->recordes),
        ])->extends('main.print-order.index');
    }

       // Reset pagination on every variable updated
       public function updated(){
        $this->resetPage();
    }
}
