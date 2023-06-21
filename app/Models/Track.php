<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Track extends Model
{
    use HasFactory;
    protected $fillable=[
        'distribution_id',
        'Book_id',
        'trace',
        'start_time',
        'end_time',
        'status',
    ];

    public function distribution()
    {
        return $this->belongsTo(Distribution::class, 'distribution_id');
    }   
    public function book()
    {
        return $this->belongsTo(Book::class, 'book_id');
    }   
}
