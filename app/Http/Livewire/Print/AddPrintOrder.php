<?php

namespace App\Http\Livewire\Print;

use App\Models\Book;
use App\Models\Grade;
use App\Models\Organization;
use App\Models\OrganizationType;
use App\Models\PrintOrder;
use App\Models\Subject;
use Livewire\Component;
use Livewire\WithFileUploads;
use Milon\Barcode\DNS1D;
use Milon\Barcode\DNS2D;
use Milon\Barcode\BarcodeGeneratorPNG;


class AddPrintOrder extends Component
{
    use WithFileUploads;
    //variables
    public $grades=[], $subjects=[],$books=[], $book_codes=[[]],$organizations=[];
    public $grade_id, $subject_id,$book_id, $grade, $subject,$barcode_start, $barcode_end,$qrcode_start, $qrcode_end, $number_of_copies,$number_of_packages=0, $expected_print_date, $actual_print_date,$organizationTypes,$organization_id,$organization_type_id,$bookPerPackage,$prefix,$sufix,$path,$path2;

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
        $this->organizationTypes=OrganizationType::all();

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
        $this->books=Book::where('grade_id',$this->grade_id)->where('subject_id',$this->subject_id)->get();
    }
    public function updatedBookId()
    {
        //check if there is print order
        $this->prefix=PrintOrder::all()->count();

        //yes
        if($this->prefix){
            //count the availbale print and add one 
            $this->prefix=$this->prefix+1;
            //check if this book is previosly printed
            // $book=PrintOrder::where('book_id',$this->book_id)->orderBy('id','desc')->first();
            $this->sufix=1;
            $this->prefix=str_pad($this->prefix,6,'0',STR_PAD_LEFT);
            $this->sufix=str_pad($this->sufix,7,'0',STR_PAD_LEFT);
            $this->qrcode_start='MoE'.$this->prefix.$this->sufix;
            $this->barcode_start=$this->prefix.$this->sufix;
        }else {
            $this->prefix=1;
            $this->sufix=1;
            $this->prefix=str_pad($this->prefix,6,'0',STR_PAD_LEFT);
            $this->sufix=str_pad($this->sufix,7,'0',STR_PAD_LEFT);
            $this->qrcode_start='MoE'.$this->prefix.$this->sufix;
            $this->barcode_start=$this->prefix.$this->sufix;
            
        }
        
    }

    public function updatedOrganizationTypeId()
    {
        $this->organizations=Organization::where('organization_type_id',$this->organization_type_id)->get();
    }
    public function updatedNumberOfCopies()
    {
        $this->barcode_end=$this->prefix.str_pad($this->number_of_copies,7,'0',STR_PAD_LEFT);
        if($this->number_of_packages){
            $this->bookPerPackage=$this->number_of_copies/$this->number_of_packages;
        }
    }
    public function updatedNumberOfPackages()
    {
        if($this->number_of_packages){
            $this->bookPerPackage=$this->number_of_copies/$this->number_of_packages;
            $this->qrcode_end='MoE'.$this->prefix.str_pad($this->number_of_packages,7,'0',STR_PAD_LEFT);
        }
    }

    public function addPrintOrder(){
      
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
      
       if (!\File::exists(public_path('barcodes'))) {
        \File::makeDirectory(public_path('barcodes'), $mode = 0777, true, true);
    }
        $barcode = new DNS2D();
        $barcodeImage = $barcode->getBarcodeSVG($this->qrcode_start, 'QRCODE');

        $storagePath = public_path('barcodes/');
        $filename = $this->barcode_start.'.svg';

        file_put_contents($storagePath . $filename, $barcodeImage);

       $this->path='/barcodes/'.$filename;

       if (!\File::exists(public_path('qrcodes'))) {
        \File::makeDirectory(public_path('qrcodes'), $mode = 0777, true, true);
    }
        $qrcodes = new DNS1D();
        $qrcodesImage = $qrcodes->getBarcodeSVG($this->barcode_start, 'C39');

        $storagePath = public_path('qrcodes/');
        $filename2 = $this->qrcode_start.'.svg';

        file_put_contents($storagePath . $filename, $qrcodesImage);

       $this->path2='/qrcodes/'.$filename2;

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
