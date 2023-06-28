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
        'start_barcode',
        'end_barcode',
        'start_qrcode',
        'end_qrcode',
        'print_status',
        'request_status',
        'expected_print_time'
    ];

    public function orderOrganization()
    {
        return $this->belongsTo(Organization::class, 'order_organization_id');
    }   
    public function book()
    {
        return $this->belongsTo(Book::class, 'book_id');
    }   

    public function printOrganization()
    {
        return $this->belongsTo(Organization::class, 'printer_organization_id');
    }  


    
}
