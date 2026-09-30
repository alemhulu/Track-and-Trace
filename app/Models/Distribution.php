<?php

namespace App\Models;

use App\Models\DistributionStep;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Distribution extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'is_active',
        'printer_id',
        'moe_id',
        'region_id',
        'zone_id',
        'woreda_id',
        'school_id',
        'step',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function printer()
    {
        return $this->belongsTo(Organization::class, 'printer_id');
    }
    public function moe()
    {
        return $this->belongsTo(Organization::class, 'moe_id');
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
    public function school()
    {
        return $this->belongsTo(Organization::class, 'school_id');
    }
    public function tracks()
    {
        return $this->hasMany(Track::class);
    }

    public function steps()
    {
        return $this->hasMany(DistributionStep::class)->orderBy('step_order');
    }

    public function scopeAccessibleBy($query, ?User $actor)
    {
        if (! $actor) {
            return $query->whereRaw('1 = 0');
        }

        if ($actor->hasNationalAccess()) {
            return $query;
        }

        $level = $actor->effectiveAccessLevel();

        return $query->where(function ($distributionQuery) use ($actor, $level) {
            if ($level === User::ACCESS_LEVEL_REGION && ! empty($actor->region_id)) {
                $distributionQuery->where('region_id', $actor->region_id);
            }

            if ($level === User::ACCESS_LEVEL_ZONE && ! empty($actor->zone_id)) {
                $distributionQuery->where('region_id', $actor->region_id)->where('zone_id', $actor->zone_id);
            }

            if ($level === User::ACCESS_LEVEL_WOREDA && ! empty($actor->woreda_id)) {
                $distributionQuery
                    ->where('region_id', $actor->region_id)
                    ->where('zone_id', $actor->zone_id)
                    ->where('woreda_id', $actor->woreda_id);
            }

            if ($level === User::ACCESS_LEVEL_ORGANIZATION && ! empty($actor->organization_id)) {
                $distributionQuery->where(function ($organizationScope) use ($actor) {
                    $organizationScope
                        ->where('school_id', $actor->organization_id)
                        ->orWhere('moe_id', $actor->organization_id)
                        ->orWhere('printer_id', $actor->organization_id);
                });
            }

            $distributionQuery->orWhereHas('steps.route', function ($routeQuery) use ($actor) {
                $routeQuery->accessibleBy($actor);
            });
        });
    }
}
