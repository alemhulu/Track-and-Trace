<?php

namespace App\Http\Livewire\BookPackage;

use Illuminate\Support\Facades\DB;
use App\Models\package;
use App\Models\Subject;
use Livewire\Component;
use Livewire\WithPagination;

class BookPackageIndex extends Component
{
    use WithPagination;
   //variables
   public $packages=[],$subjects=[];
   public $column, $recordes, $sortType;
   public $total,$sent,$received,$available=0;

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
       
    //    $this->subjects=package::selectRaw('id,subject_id,grade_id,balance,sent,received')->groupBy('subject_id')->get();
    $this->subjects=Package::with('subject','grade')->get()->groupBy('subject_id')->toArray();
    //    dd($this->subjects[11]);
       $this->recordes=5;
       $this->column='';
       $this->total=package::sum('no_of_books');
       $this->sent=package::sum('sent');
       $this->received=package::sum('received');
       $this->available=$this->total+$this->received-$this->sent;
   }
    public function render()
    {
            
        return view('livewire.book-package.book-package-index')->extends('main.book-package.index');
    }
}
