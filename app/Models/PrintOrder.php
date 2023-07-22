<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrintOrder extends Model
{
    use HasFactory;
    protected $fillable=[
        'order_organization_id',
        'book_id',
        'printer_organization_id',
        'no_of_books',
        'no_of_packages',
        'Book_codes',
        'start_barcode',
        'end_barcode',
        'start_qrcode',
        'end_qrcode',
        'print_status',
        'request_status',
        'expected_print_time',
        'barcode_start',
        'barcode_end',
        'qrcode_start',
        'qrcode_end',
        'book_per_package'
    ];

    public function orderOrganization()
    {
        return $this->belongsTo(Organization::class, 'order_organization_id');
    }   
    public function book()
    {
        return $this->belongsTo(Book::class, 'book_id');
    }   
    // public function bookPackage()
    // {
    //     return $this->belongsTo(Book::class, 'book_id')->select('id','grade_id','subject_id','isbn','volume','edition','front_cover_location');
    // }  

    public function printOrganization()
    {
        return $this->belongsTo(Organization::class, 'printer_organization_id');
    }  

    protected $casts=[
        'Book_codes' => 'json',
    ];

    
}
