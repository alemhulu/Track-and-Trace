<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory;
    protected $fillable=[
        'ware_house_id',
        'book_id',
        'user_id',
        'available',
        'sent',
        'balance'
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
