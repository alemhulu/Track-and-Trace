<?php

namespace App\Http\Livewire\BookPackage;

use App\Models\Package;
use Livewire\Component;
use Livewire\WithPagination;


class PackageAvailableInfo extends Component
{
 use WithPagination;
 
          //variables
   public $packages=[];
   public $package;
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
   public function mount($id)
   {
       $this->package=Package::where('id',$id)->with('printOrder.book','organization','organization.assignedUser','subject')->get()->toArray()[0];
    // dd($this->package);
    // $this->packages=Package::with('printOrder')->get()->groupBy('subject_id')->toArray();
    // $this->packages=Package::with('printOrder.book','organization','organization.assignedUser','subject')->inRandomOrder()->get()->groupBy('print_order_id')->toArray();
    // dd($this->packages);
       $this->recordes=5;
       $this->column='';
   }
    public function render()
    {
        return view('livewire.book-package.package-available-info')->extends('main.book-package.index');
    }
}
