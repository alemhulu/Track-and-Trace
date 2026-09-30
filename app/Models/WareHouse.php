<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WareHouse extends Model
{
    use HasFactory;
    protected $fillable = [
        'branch',
        'organization_id',
        'assigned_user_id',
        'country_id',
        'region_id',
        'zone_id',
        'woreda_id',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }
    public function country()
    {
        return $this->belongsTo(Country::class, 'county_id');
    }
    public function region()
    {
        return $this->belongsTo(Region::class, 'region_id');
    }
    public function zone()
    {
        return $this->belongsTo(Zone::class, 'zone_id');
    }
    public function woreda()
    {
        return $this->belongsTo(Woreda::class, 'woreda_id');
    }
    public function packages()
    {
        return $this->hasMany(Package::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function scopeAccessibleBy($query, User $actor)
    {
        if ($actor->hasNationalAccess()) {
            return $query;
        }

        $level = $actor->effectiveAccessLevel();

        if ($level === User::ACCESS_LEVEL_REGION && ! empty($actor->region_id)) {
            return $query->where('region_id', $actor->region_id);
        }

        if ($level === User::ACCESS_LEVEL_ZONE && ! empty($actor->zone_id)) {
            return $query->where('region_id', $actor->region_id)->where('zone_id', $actor->zone_id);
        }

        if ($level === User::ACCESS_LEVEL_WOREDA && ! empty($actor->woreda_id)) {
            return $query
                ->where('region_id', $actor->region_id)
                ->where('zone_id', $actor->zone_id)
                ->where('woreda_id', $actor->woreda_id);
        }

        if ($level === User::ACCESS_LEVEL_ORGANIZATION && ! empty($actor->organization_id)) {
            return $query->where('organization_id', $actor->organization_id);
        }

        return $query->whereRaw('1 = 0');
    }
}
