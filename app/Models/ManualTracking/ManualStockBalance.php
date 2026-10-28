<?php

namespace App\Models\ManualTracking;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Copies of one title edition held by one organization. Always equal to the sum of
 * that holder's stock ledger entries; written only by ManualStockService.
 */
class ManualStockBalance extends ManualBaseModel
{
    protected $fillable = [
        'organization_id',
        'manual_book_id',
        'edition',
        'on_hand',
        'in_transit_out',
    ];

    protected $casts = [
        'on_hand' => 'integer',
        'in_transit_out' => 'integer',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(ManualBook::class, 'manual_book_id');
    }

    public function scopeAccessibleBy(Builder $query, ?User $actor): Builder
    {
        if (! $actor) {
            return $query->whereRaw('1 = 0');
        }

        if ($actor->hasNationalAccess() || $actor->hasRole('Admin')) {
            return $query;
        }

        return $query->whereIn('organization_id', Organization::query()->accessibleBy($actor)->select('id'));
    }
}
