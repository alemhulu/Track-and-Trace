<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class woreda extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'zone_id',
        'region_id',
        'country_id',
    ];

    public function zone()
    {
        return $this->belongsTo(zone::class, 'zone_id');
    }   

    public function region()
    {
        return $this->belongsTo(region::class, 'region_id');
    }   

    public function country()
    {
        return $this->belongsTo(country::class, 'country_id');
    }  
}

