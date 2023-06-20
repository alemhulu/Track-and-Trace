<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class package extends Model
{
    use HasFactory;
    protected $fillable=[
        'store_id',
        'book_id',
        'user_id',
        'available',
        'sent',
        'balance'
    ];
}
