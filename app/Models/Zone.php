<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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
        return $this->hasMany(Woreda::class);
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

    public function scopeAccessibleBy(Builder $query, User $actor): Builder
    {
        $scope = $actor->locationScope();

        return $query
            ->when($scope['region_id'] !== null, fn (Builder $zones) => $zones->where('region_id', $scope['region_id']))
            ->when($scope['zone_id'] !== null, fn (Builder $zones) => $zones->whereKey($scope['zone_id']));
    }
}
