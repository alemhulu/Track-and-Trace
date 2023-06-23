<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Woreda extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'zone_id',
        'region_id',
        'country_id',
    ];

    public function kebeles()
    {
        return $this->hasMany(Kebele::class);
    } 

    public function zone()
    {
        return $this->belongsTo(Zone::class, 'zone_id');
    }   

    public function region()
    {
        return $this->belongsTo(Region::class, 'region_id');
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
