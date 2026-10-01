<?php

namespace App\Models\ManualTracking;

use App\Models\Country;
use App\Models\Organization;
use App\Models\Region;
use App\Models\User;
use App\Models\Woreda;
use App\Models\Zone;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ManualDistribution extends ManualBaseModel
{
    protected $fillable = [
        'reference',
        'distributed_by',
        'organization_id',
        'country_id',
        'region_id',
        'zone_id',
        'woreda_id',
        'destination_organization_id',
        'destination_country_id',
        'destination_region_id',
        'destination_zone_id',
        'destination_woreda_id',
        'distributed_at',
        'remarks',
    ];

    protected $casts = [
        'distributed_at' => 'datetime',
    ];

    public function lines(): HasMany
    {
        return $this->hasMany(\App\Models\ManualTracking\ManualDistributionLine::class, 'manual_distribution_id');
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

    public function destinationOrganization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'destination_organization_id');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'region_id');
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class, 'zone_id');
    }

    public function woreda(): BelongsTo
    {
        return $this->belongsTo(Woreda::class, 'woreda_id');
    }

    public function destinationCountry(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'destination_country_id');
    }

    public function destinationRegion(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'destination_region_id');
    }

    public function destinationZone(): BelongsTo
    {
        return $this->belongsTo(Zone::class, 'destination_zone_id');
    }

    public function destinationWoreda(): BelongsTo
    {
        return $this->belongsTo(Woreda::class, 'destination_woreda_id');
    }

    public function scopeAccessibleBy(Builder $query, ?User $actor): Builder
    {
        if (! $actor) {
            return $query->whereRaw('1 = 0');
        }

        if ($actor->hasNationalAccess() || $actor->hasRole('Admin')) {
            return $query;
        }

        if (! empty($actor->organization_id)) {
            return $query->where(function (Builder $scoped) use ($actor): void {
                $scoped->where('organization_id', $actor->organization_id)
                    ->orWhere('destination_organization_id', $actor->organization_id)
                    ->orWhere('distributed_by', $actor->id);
            });
        }

        return $query->where('distributed_by', $actor->id);
    }
}
