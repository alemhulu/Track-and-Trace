<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'name'
    ];

    public function grades()
     {
         return $this->belongsToMany(Grade::class, 'grade_subjects');
     }

     public function books()
     {
        return $this->hasMany(Book::class);
     }
     public function subjects()
     {
        return $this->hasMany(package::class);
     }

     public function pakages()
     {
        return $this->hasMany(package::class, 'subject_id');
     }
}
