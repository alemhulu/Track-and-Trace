<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name'
    ];

    public function regions()
    {
        return $this->hasMany(region::class);
    }

    public function zone()
    {
        return $this->hasMany(zone::class);
    }

    public function woreda()
    {
        return $this->hasMany(woreda::class);
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

