<?php

namespace App\Http\Livewire\Print;

use App\Models\Book;
use App\Models\Grade;
use App\Models\Organization;
use App\Models\OrganizationType;
use App\Models\PrintOrder;
use App\Models\Subject;
use Illuminate\Support\Facades\Storage;
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
    public $grade_id, $subject_id,$book_id, $grade, $subject,$barcode_start, $barcode_end,$qrcode_start, $qrcode_end, $number_of_copies,$number_of_packages, $expected_print_date, $actual_print_date,$organizationTypes,$organization_id,$organization_type_id,$bookPerPackage,$prefix,$sufix,$path,$path2;

    protected $rules = [
        'grade_id' => 'required',
        'subject_id' => 'required',
        'book_id' => 'required',
        'barcode_start' => 'required',
        'barcode_end' => 'required',
        'qrcode_start' => 'required',
        'qrcode_end' => 'required',
        'number_of_copies' => 'required',
        'number_of_packages' => 'required|numeric|min:1',
        'expected_print_date' => 'required',
        'organization_id' => 'required'
    ];

    public function mount()
    {
        $this->grades=Grade::all();
        $this->organizationTypes=OrganizationType::where('name', 'Printer')->get();

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
            $this->qrcode_start=$this->prefix.$this->sufix;
            $this->barcode_start=$this->prefix.$this->sufix;
        }else {
            $this->prefix=1;
            $this->sufix=1;
            $this->prefix=str_pad($this->prefix,6,'0',STR_PAD_LEFT);
            $this->sufix=str_pad($this->sufix,7,'0',STR_PAD_LEFT);
            $this->qrcode_start=$this->prefix.$this->sufix;
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
            $this->qrcode_end=$this->prefix.str_pad($this->number_of_packages,7,'0',STR_PAD_LEFT);
        }
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
        'no_of_books' => $this->number_of_copies,
        'no_of_packages' => $this->number_of_packages,
        'order_organization_id' => 1,
        'printer_organization_id' => $this->organization_id,
        'expected_print_time' => $this->expected_print_date,
        'print_status' =>0,
        'request_status' => 0,
       ];
       $printBatch=PrintOrder::create($data);
     
       $Book_codes = [];
       for ($q=0; $q < $this->number_of_packages ; $q++) { 
        $barcode = new DNS2D();
        $qrcodesImage = $barcode->getBarcodeSVG('G-'. $this->grade->name.' '. $this->subject->name. ': '. $this->qrcode_start, 'QRCODE');
        $this->qrcode_start = str_pad($this->qrcode_start,13,'0',STR_PAD_LEFT);
        $qr = $this->qrcode_start.'.svg';
        Storage::disk('public')->put('printOrders/'.$printBatch->id.'/'.($q+1).'/'.$this->qrcode_start.'.svg', $qrcodesImage);
        $this->qrcode_start ++;

        $barcodes = [];
        for ($b=0; $b < $this->bookPerPackage; $b++) { 
            $qrcodes = new DNS1D();
            $barcodeImage = $qrcodes->getBarcodeSVG($this->barcode_start, 'C39', 1, 50);
            $this->barcode_start = str_pad($this->barcode_start,13,'0',STR_PAD_LEFT);
            $barcodes[]=$this->barcode_start.'.svg';
            Storage::disk('public')->put('printOrders/'.$printBatch->id.'/'.($q+1).'/barcods/'.$this->barcode_start.'.svg', $barcodeImage);
            $this->barcode_start ++;
           }
           
           $Book_codes[$q+1] = [ 'barcodes' => $barcodes, 'QR' => $qr ];
    }

    $printBatch['Book_codes'] =$Book_codes;    
    $printBatch->save();
    // dd( $Book_codes );

    $this->alertSuccess();
    sleep(1);
    return redirect()->route('print-order.list');
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
