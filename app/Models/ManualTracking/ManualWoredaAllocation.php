<?php

namespace App\Models\ManualTracking;

use App\Models\Region;
use App\Models\User;
use App\Models\Woreda;
use App\Models\Zone;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Copies of one title and edition a woreda is to receive in an academic year: its share of
 * the zone allocation, as the regional education bureau split it.
 */
class ManualWoredaAllocation extends ManualBaseModel
{
    protected $casts = [
        'quantity' => 'integer',
        'source_row' => 'integer',
    ];

    public function book(): BelongsTo
    {
        return $this->belongsTo(ManualBook::class, 'manual_book_id');
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function woreda(): BelongsTo
    {
        return $this->belongsTo(Woreda::class);
    }

    public function import(): BelongsTo
    {
        return $this->belongsTo(ManualAllocationImport::class, 'manual_allocation_import_id');
    }

    public function scopeAccessibleBy(Builder $query, ?User $actor): Builder
    {
        if (! $actor) {
            return $query->whereRaw('1 = 0');
        }

        $scope = $actor->hasRole('Admin') ? ['region_id' => null, 'zone_id' => null, 'woreda_id' => null] : $actor->locationScope();

        return $query
            ->when($scope['region_id'] !== null, fn (Builder $rows) => $rows->where('manual_woreda_allocations.region_id', $scope['region_id']))
            ->when($scope['zone_id'] !== null, fn (Builder $rows) => $rows->where('manual_woreda_allocations.zone_id', $scope['zone_id']))
            ->when(($scope['woreda_id'] ?? null) !== null, fn (Builder $rows) => $rows->where('manual_woreda_allocations.woreda_id', $scope['woreda_id']));
    }
}
