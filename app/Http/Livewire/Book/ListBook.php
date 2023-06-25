<?php

namespace App\Http\Livewire\Book;

use App\Models\Book;
use Livewire\Component;
use Livewire\WithPagination;
class ListBook extends Component
{ 
    use WithPagination;
    //variables
    public $books=[];
    public $column, $recordes, $sortType;
    public function mount()
    {
        $this->books=Book::all();
        $this->recordes=5;
        $this->column='';
    }
    public function render()
    {
        return view('livewire.book.list-book',
        ['books'=>Book::
        when($this->column,function($q,$column){
            return $q->orderBy($this->column,$this->sortType);
        })->paginate($this->recordes)

    ])->extends('main.book.index');
    }
}
