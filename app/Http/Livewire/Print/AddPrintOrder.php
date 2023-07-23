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
    public $grade_id, $subject_id,$book_id, $grade, $subject,$barcode_start, $barcode_end,$qrcode_start, $qrcode_end, $number_of_copies, $expected_print_date, $actual_print_date,$organization_id,$book_per_package,$prefix,$sufix,$path,$path2,$no_of_packages;

    protected $rules = [
        'grade_id' => 'required',
        'subject_id' => 'required',
        'book_id' => 'required',
        'barcode_start' => 'required',
        'barcode_end' => 'required',
        'qrcode_start' => 'required',
        'qrcode_end' => 'required',
        'number_of_copies' => 'required',
        'no_of_packages' => 'required|numeric|min:1',
        'expected_print_date' => 'required',
        'organization_id' => 'required'
    ];

    public function mount()
    {
        $this->grades=Grade::all();
        $this->organizations=Organization::where('organization_type_id', '6')->get();

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
        if($this->book_per_package){
            $this->no_of_packages=$this->number_of_copies/$this->book_per_package;
        }
    }

    public function updatedBookPerPackage()
    {
        if($this->book_per_package){
            $this->no_of_packages=$this->number_of_copies/$this->book_per_package;
            $this->qrcode_end=$this->prefix.str_pad($this->no_of_packages,7,'0',STR_PAD_LEFT);
        }
    }

    public function addPrintOrder(){
    //   $this->validate();
       $data=[
        'grade_id'=>$this->grade_id,
        'subject_id'=>$this->grade_id,
        'book_id' => $this->book_id,
        'barcode_start' => $this->barcode_start,
        'barcode_end' => $this->barcode_end,
        'qrcode_start' => $this->qrcode_start,
        'qrcode_end' => $this->qrcode_end,
        'no_of_books' => $this->number_of_copies,
        'book_per_package'=>$this->book_per_package,
        'no_of_packages' => $this->no_of_packages,
        'order_organization_id' => 1,
        'printer_organization_id' => $this->organization_id,
        'expected_print_time' => $this->expected_print_date,
        'print_status' =>0,
        'request_status' => 0,
       ];
       $printBatch=PrintOrder::create($data);
       $Book_codes = [];
       for ($q=0; $q < $this->no_of_packages ; $q++) { 
        $barcode = new DNS2D();
        $this->qrcode_start = str_pad($this->qrcode_start,13,'0',STR_PAD_LEFT);
        $qrcodesImage = $barcode->getBarcodeSVG('G-'. $this->grade->name.' '. $this->subject->name. ': '. $this->qrcode_start, 'QRCODE');
        $qr = $this->qrcode_start.'.svg';
        Storage::disk('public')->put('printOrders/'.$printBatch->id.'/'.($q+1).'/'.$this->qrcode_start.'.svg', $qrcodesImage);
        $this->qrcode_start ++;

        $barcodes = [];
        for ($b=0; $b < $this->book_per_package; $b++) { 
            $qrcodes = new DNS1D();
            $this->barcode_start = str_pad($this->barcode_start,13,'0',STR_PAD_LEFT);
            $barcodeImage = $qrcodes->getBarcodeSVG($this->barcode_start, 'C39', 1, 50);
            $barcodes[]=$this->barcode_start.'.svg';
            Storage::disk('public')->put('printOrders/'.$printBatch->id.'/'.($q+1).'/barcods/'.$this->barcode_start.'.svg', $barcodeImage);
            $this->barcode_start ++;
           }
           
           $Book_codes[$q+1] = [ 'barcodes' => $barcodes, 'QR' => $qr , 'status'=>1 ];
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
