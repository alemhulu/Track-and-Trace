<?php

namespace App\Models\ManualTracking;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class ManualStockLedger extends ManualBaseModel
{
    protected $fillable = [
        'manual_book_id',
        'manual_book_package_id',
        'edition',
        'movement_type',
        'movement_qty',
        'balance_after',
        'ref_type',
        'ref_id',
        'manual_distribution_line_id',
        'acted_by',
        'organization_id',
        'country_id',
        'region_id',
        'zone_id',
        'woreda_id',
        'notes',
        'client_uuid',
        'occurred_at',
    ];

    protected $casts = [
        'movement_qty' => 'integer',
        'balance_after' => 'integer',
        'occurred_at' => 'datetime',
    ];

    public function scopeAccessibleBy(Builder $query, ?User $actor): Builder
    {
        if (! $actor) {
            return $query->whereRaw('1 = 0');
        }

        if ($actor->hasNationalAccess() || $actor->hasRole('Admin')) {
            return $query;
        }

        return $query->whereIn('manual_stock_ledgers.organization_id', Organization::query()->accessibleBy($actor)->select('id'));
    }
}
