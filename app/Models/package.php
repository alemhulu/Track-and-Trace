<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory;
    protected $fillable=[
       'ware_house_id',
       'print_order_id',
       'sender_organization_id',
       'receiver_organization_id',
       'step',
       'Book_codes',
       'received',
       'sent',
       'balance',
       'no_of_books',
       'books_per_package',
       'qrcode_start',
       'qrcode_end',
       'barcode_start',
       'barcode_end',
       'expected_send_time',
       'actual_send_time',
       'expected_delivery_school_time',
       'actual_delivery_school_time',
       'request_status',
       'delivery_status',
       'description',
    ];

    protected $casts=[
        'Book_codes' => 'json',
    ];

    public function wareHouse()
    {
        return $this->belongsTo(WareHouse::class, 'ware_house_id');
    }   
    public function book()
    {
        return $this->belongsTo(Book::class, 'book_id');
    }   
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }  
}
