<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Zone extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'is_subcity',
        'region_id',
        'country_id',
        'name'
    ];

     public function woredas()
    {
        return $this->hasMany(woreda::class);
    }

    public function region()
    {
        return $this->belongsTo(region::class, 'region_id');
    }

    public function country()
    {
        return $this->belongsTo(country::class, 'country_id');
    }

    public function distributions()
    {
        return $this->hasMany(Distribution::class);
    }

    public function organizations()
    {
        return $this->hasMany(Organization::class);
    }

    public function wareHouses()
    {
        return $this->hasMany(WareHouse::class);
    } 
}
