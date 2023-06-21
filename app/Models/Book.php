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

    public function grade()
    {
        return $this->belongsTo(Grade::class, 'grade_id');
    }
    public function subject()
    {
        return $this->belongsTo(Subject::class, 'grade_id');
    }
    public function packages()
    {
        return $this->hasMany(Package::class);
    } 
}
