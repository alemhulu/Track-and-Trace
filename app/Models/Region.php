<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'is_city',
        'name',
        'country_id',
    ];

      /**
     * Get all of the zones for the Region
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function zones()
    {
        return $this->hasMany(Zone::class);
    }

    public function woreda()
    {
        return $this->hasMany(Woreda::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
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
