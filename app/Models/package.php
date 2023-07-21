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
       'subject_id',
       'grade_id'
    ];

    protected $casts=[
        'Book_codes' => 'json',
    ];

    public function wareHouse()
    {
        return $this->belongsTo(WareHouse::class, 'ware_house_id');
    }   

    public function user()
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }  
    public function subject()
    {
        return $this->belongsTo(Subject::class)->select('id','name');
    }  
    public function grade()
    {
        return $this->belongsTo(Grade::class);
    }  
    public function printOrder()
    {
        return $this->belongsTo(PrintOrder::class,'print_order_id')->select('id','book_id','qrcode_start','qrcode_end','no_of_packages');
    }
    public function organization()
    {
        return $this->belongsTo(Organization::class,'sender_organization_id')->select('id','name','email','assigned_user_id','logo','telephone');
    }
}
