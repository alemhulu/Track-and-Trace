<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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

    // Woredas do not always carry region_id, so the region is matched through the zone
    public function scopeAccessibleBy(Builder $query, User $actor): Builder
    {
        $scope = $actor->locationScope();

        return $query
            ->when($scope['region_id'] !== null, fn (Builder $woredas) => $woredas->whereHas('zone', fn (Builder $zone) => $zone->where('region_id', $scope['region_id'])))
            ->when($scope['zone_id'] !== null, fn (Builder $woredas) => $woredas->where('zone_id', $scope['zone_id']))
            ->when($scope['woreda_id'] !== null, fn (Builder $woredas) => $woredas->whereKey($scope['woreda_id']));
    }
}
