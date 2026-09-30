<?php

namespace App\Models;

use App\Models\User as ModelsUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as AuthUser;

class Organization extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'website',
        'logo',
        'telephone',
        'bank',
        'coordinate',
        'coordinate',
        'gps',
        'year',
        'old_code',
        'new_code',
        'facilities',
        'assigned_user_id',
        'manager_name',
        'phone',
        'location',
        'status',
        'organization_type_id',
        'country_id',
        'region_id',
        'zone_id',
        'woreda_id',
        'sector_id',
        'ownership_id',
        'user_id',
    ];

    public function users()
    {
        return $this->hasMany(ModelsUser::class);
    }


    public function contact()
    {
        return $this->belongsTo(ModelsUser::class, 'assigned_user_id');
    }

    public function woreda()
    {
        return $this->belongsTo(Woreda::class, 'woreda_id');
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

    public function ownership()
    {
        return $this->belongsTo(Ownership::class, 'region_id');
    }

    public function organizationType()
    {
        return $this->belongsTo(OrganizationType::class, 'organization_type_id');
    }

    public function assignedUser()
    {
        return $this->belongsTo(AuthUser::class, 'assigned_user_id')->select('id', 'name', 'email', 'phone', 'profile_photo_path');
    }

    public function scopeAccessibleBy($query, ModelsUser $actor)
    {
        if ($actor->hasNationalAccess()) {
            return $query;
        }

        $level = $actor->effectiveAccessLevel();

        if ($level === ModelsUser::ACCESS_LEVEL_REGION && ! empty($actor->region_id)) {
            return $query->where('region_id', $actor->region_id);
        }

        if ($level === ModelsUser::ACCESS_LEVEL_ZONE && ! empty($actor->zone_id)) {
            return $query->where('region_id', $actor->region_id)->where('zone_id', $actor->zone_id);
        }

        if ($level === ModelsUser::ACCESS_LEVEL_WOREDA && ! empty($actor->woreda_id)) {
            return $query
                ->where('region_id', $actor->region_id)
                ->where('zone_id', $actor->zone_id)
                ->where('woreda_id', $actor->woreda_id);
        }

        if ($level === ModelsUser::ACCESS_LEVEL_ORGANIZATION && ! empty($actor->organization_id)) {
            return $query->where('id', $actor->organization_id);
        }

        return $query->whereRaw('1 = 0');
    }
}
