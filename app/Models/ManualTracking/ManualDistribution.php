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

/**
 * A shipment of manual-tracked books from one organization to another.
 * Dispatched by the sender, then counted and confirmed by the receiver.
 * (`distributed_by` / `distributed_at` are the dispatcher and dispatch time.)
 */
class ManualDistribution extends ManualBaseModel
{
    public const STATUS_DISPATCHED = 'dispatched';
    public const STATUS_RECEIVED = 'received';
    public const STATUS_RECEIVED_WITH_DISCREPANCY = 'received_with_discrepancy';

    public const STATUSES = [
        self::STATUS_DISPATCHED => 'In Transit',
        self::STATUS_RECEIVED => 'Received',
        self::STATUS_RECEIVED_WITH_DISCREPANCY => 'Received with Discrepancy',
    ];

    protected $fillable = [
        'reference',
        'status',
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
        'carrier',
        'vehicle_no',
        'driver_name',
        'driver_phone',
        'waybill_no',
        'expected_at',
        'received_at',
        'received_by',
        'receipt_notes',
        'client_uuid',
    ];

    protected $casts = [
        'distributed_at' => 'datetime',
        'expected_at' => 'datetime',
        'received_at' => 'datetime',
    ];

    public function isInTransit(): bool
    {
        return $this->status === self::STATUS_DISPATCHED;
    }

    public function dispatcher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'distributed_by');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

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

        // Shipments leaving or arriving at any organization inside the actor's area
        $organizationIds = Organization::query()->accessibleBy($actor)->select('id');

        return $query->where(function (Builder $scoped) use ($actor, $organizationIds): void {
            $scoped->whereIn('organization_id', $organizationIds)
                ->orWhereIn('destination_organization_id', $organizationIds)
                ->orWhere('distributed_by', $actor->id);
        });
    }
}
