<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;
    protected $fillable=[
        'grade_id',
        'subject_id',
        'isbn',
        'volume',
        'edition',
        'book_type',
        'print_type',
        'paper_size',
        'file_location',
        'front_cover_location',
        'back_cover_location',
    ];
}
