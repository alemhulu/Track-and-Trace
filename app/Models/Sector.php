<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sector extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'name',
        'code',
        'user_id'
    ];
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_sectors');
    }
}
