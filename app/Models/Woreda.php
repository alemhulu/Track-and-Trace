<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Woreda extends Model
{
    use HasFactory;

    protected $fillable=[
        'name',
        'code',
        'region_id',
        'zone_id'
    ];
}
