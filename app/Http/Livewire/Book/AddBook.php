<?php

namespace App\Http\Livewire\Book;

use App\Models\Book;
use App\Models\Grade;
use Livewire\Component;
use Livewire\WithFileUploads;

class AddBook extends Component
{
      use WithFileUploads;
    //variables
    public $grades=[], $subjects=[],$books=[];
    public $grade_id, $subject_id,$book_id, $grade, $subject, $book_type,$edition, $volume, $isbn, $paper_size, $file, $front_cover, $back_cover, $print_type;

    protected $rules = [
        'grade_id' => 'required',
        'subject_id' => 'required',
        'book_type' => 'required',
        'edition' => 'required',
        'isbn' => 'required',
        'file' => 'required',
        'print_type' => 'required',
        'paper_size' => 'required',
        'front_cover' => 'required|image',
        'back_cover' => 'required|image',

    ];

    public function mount()
    {
        $this->grades=Grade::all();
    }
    public function render()
    {
        return view('livewire.book.add-book')->extends('main.book.index');
    }
    public function updatedGradeId()
    {
        $this->grade=Grade::findOrFail($this->grade_id);
        $this->subjects=$this->grade->subjects;
    }

    public function addBook(){
        $this->validate();
       $data=[
        'grade_id'=>$this->grade_id,
        'subject_id'=>$this->subject_id,
        'book_type'=>$this->book_type,
        'print_type'=>$this->print_type,
        'paper_size'=>$this->paper_type,
        'edition'=>$this->edition,
        'volume'=>$this->volume,
        'isbn'=>$this->isbn,
       ];
       $book=Book::create($data);
       $frontCoverFileName=$this->front_cover->getClientOriginalName();
       $backCoverFileName=$this->back_cover->getClientOriginalName();
       $fileFileName=$this->file->getClientOriginalName();

       $frontCoverFilePath = $this->front_cover->storeAs('book/'.$book->id, $frontCoverFileName, 'public');
       $BackCoverFilePath = $this->back_cover->storeAs('book/'.$book->id, $backCoverFileName, 'public');
       $fileFilePath = $this->file->storeAs('book/'.$book->id, $fileFileName, 'public');

       $file['front_cover'] = $frontCoverFilePath;
       $file['back_cover'] = $BackCoverFilePath;
       $file['file'] = $fileFilePath;

       $book['front_cover_location']='/storage/'.$file['front_cover'];
       $book['back_cover_location']='/storage/'.$file['back_cover'];
       $book['file_location']='/storage/'.$file['file'];

       $book->save();
       $this->alertSuccess();
    }

    public function alertError( $name ) {
        $this->dispatchBrowserEvent(
            'alert', ['type' => 'error',  'message' => $name.' error']
        );
    }

    public function alertSuccess() {
        $this->dispatchBrowserEvent(
            'alert',
            ['type' => 'success',  'message' => 'Book Added Successfully!']
        );
    }

    public function alertWarning() {
        $this->dispatchBrowserEvent(
            'alert',
            ['type' => 'warning',  'message' => 'Book will be deleted after save !!']
        );
    }
}
