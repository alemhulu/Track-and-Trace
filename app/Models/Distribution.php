<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Distribution extends Model
{
    use HasFactory;

    protected $fillable = [
       'is_active',
       'printer_id',
       'moe_id',
       'region_id',
       'zone_id',
       'woreda_id',
       'school_id',
       'step',
    ];

    public function steps()
    {
        return $this->hasMany(distribution::class);
    }
}
