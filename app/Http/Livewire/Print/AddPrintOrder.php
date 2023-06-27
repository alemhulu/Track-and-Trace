<?php

namespace App\Http\Livewire\Print;

use App\Models\Book;
use App\Models\Grade;
use App\Models\PrintOrder;
use App\Models\Subject;
use Livewire\Component;
use Livewire\WithFileUploads;

class AddPrintOrder extends Component
{
    use WithFileUploads;
    //variables
    public $grades=[], $subjects=[],$books=[], $book_codes=[[]];
    public $grade_id, $subject_id,$book_id, $grade, $subject,$barcode_start, $barcode_end,$qrcode_start, $qrcode_end, $number_of_copies,$number_of_packages, $expected_print_date, $actual_print_date;

    protected $rules = [
        'grade_id' => 'required',
        'subject_id' => 'required',
        'book_id' => 'required',
        'barcode_start' => 'required',
        'barcode_end' => 'required',
        'qrcode_start' => 'required',
        'qrcode_end' => 'required',
        'number_of_copies' => 'required',
        'number_of_packages' => 'required',
        'expected_print_date' => 'required',
    ];

    public function mount()
    {
        $this->grades=Grade::all();
    }
    public function render()
    {
        return view('livewire.print.add-print-order')->extends('main.print-order.index');
    }

    public function updatedGradeId()
    {
        $this->grade=Grade::findOrFail($this->grade_id);
        $this->subjects=$this->grade->subjects;
        $this->subject_id='';
        $this->book_id='';
    }
    public function updatedSubjectId()
    {
        $this->subject=Subject::findOrFail($this->subject_id);
        $this->books=$this->subject->books;
        $this->book_id='';
    }

    public function addPrintOrder(){
        $this->validate();
       $data=[
        'grade_id'=>$this->grade_id,
        'subject_id'=>$this->grade_id,
        'book_id' => $this->book_id,
        'barcode_start' => $this->barcode_start,
        'barcode_end' => $this->barcode_end,
        'qrcode_start' => $this->qrcode_start,
        'qrcode_end' => $this->qrcode_end,
        'number_of_copies' => $this->number_of_copies,
        'number_of_packages' => $this->number_of_packages,
        'expected_print_date' => $this->expected_print_date,
       ];
       $printOrder=PrintOrder::create($data);

    //    $frontCoverFileName=$this->front_cover->getClientOriginalName();
    //    $backCoverFileName=$this->back_cover->getClientOriginalName();
    //    $fileFileName=$this->file->getClientOriginalName();

    //    $frontCoverFilePath = $this->front_cover->storeAs('book/'.$book->id, $frontCoverFileName, 'public');
    //    $BackCoverFilePath = $this->back_cover->storeAs('book/'.$book->id, $backCoverFileName, 'public');
    //    $fileFilePath = $this->file->storeAs('book/'.$book->id, $fileFileName, 'public');

    //    $file['front_cover'] = $frontCoverFilePath;
    //    $file['back_cover'] = $BackCoverFilePath;
    //    $file['file'] = $fileFilePath;

    //    $book['front_cover_location']='/storage/'.$file['front_cover'];
    //    $book['back_cover_location']='/storage/'.$file['back_cover'];
    //    $book['file_location']='/storage/'.$file['file'];

    //    $book->save();
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
}
