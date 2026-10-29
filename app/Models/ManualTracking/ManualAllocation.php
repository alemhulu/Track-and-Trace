<?php

namespace App\Models\ManualTracking;

use App\Models\Region;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Copies of one title and edition a region is to receive in an academic year. A row with a
 * zone is that zone's share of the region's allocation; a row without one is the region's.
 */
class ManualAllocation extends ManualBaseModel
{
    protected $casts = [
        'quantity' => 'integer',
        'type_confirmed' => 'boolean',
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

    public function import(): BelongsTo
    {
        return $this->belongsTo(ManualAllocationImport::class, 'manual_allocation_import_id');
    }

    public function scopeAccessibleBy(Builder $query, ?User $actor): Builder
    {
        if (! $actor) {
            return $query->whereRaw('1 = 0');
        }

        $scope = $actor->hasRole('Admin') ? ['region_id' => null, 'zone_id' => null] : $actor->locationScope();

        // Zone users see their region's totals and their own zone's share
        return $query
            ->when($scope['region_id'] !== null, fn (Builder $rows) => $rows->where('manual_allocations.region_id', $scope['region_id']))
            ->when($scope['zone_id'] !== null, fn (Builder $rows) => $rows->where(fn (Builder $zone) => $zone
                ->whereNull('manual_allocations.zone_id')->orWhere('manual_allocations.zone_id', $scope['zone_id'])));
    }

    public static function latestYear(): ?string
    {
        return static::query()->max('academic_year');
    }

    /**
     * Copies sent into each region from outside it, keyed "region:book" with dispatched and
     * received counts per edition. Transfers inside a region are not counted, so a copy is
     * counted once on its way in however far it then travels.
     *
     * @param  array<int>|null  $regionIds
     * @param  array<int>|null  $bookIds
     * @return Collection<string, object>
     */
    public static function inboundProgress(?array $regionIds = null, ?array $bookIds = null): Collection
    {
        return static::inbound('region', $regionIds, $bookIds)->keyBy(fn ($row) => $row->region_id.':'.$row->manual_book_id);
    }

    /**
     * The same for zones: copies sent into each zone from outside it, keyed "zone:book".
     *
     * @param  array<int>|null  $zoneIds
     * @param  array<int>|null  $bookIds
     * @return Collection<string, object>
     */
    public static function inboundZoneProgress(?array $zoneIds = null, ?array $bookIds = null): Collection
    {
        return static::inbound('zone', $zoneIds, $bookIds)->keyBy(fn ($row) => $row->zone_id.':'.$row->manual_book_id);
    }

    private static function inbound(string $level, ?array $placeIds, ?array $bookIds): Collection
    {
        $own = "dist.{$level}_id";
        $destination = "dist.destination_{$level}_id";

        return DB::table('manual_distribution_lines as line')
            ->join('manual_distributions as dist', 'dist.id', '=', 'line.manual_distribution_id')
            ->whereNotNull($destination)
            ->where(fn ($inbound) => $inbound->whereNull($own)->orWhereColumn($own, '!=', $destination))
            ->when($placeIds !== null, fn ($q) => $q->whereIn($destination, $placeIds))
            ->when($bookIds !== null, fn ($q) => $q->whereIn('line.manual_book_id', $bookIds))
            ->groupBy($destination, 'line.manual_book_id')
            ->selectRaw("{$destination} as {$level}_id, line.manual_book_id")
            ->selectRaw('SUM(line.quantity) as sent_student, SUM(line.teacher_guides) as sent_teacher')
            ->selectRaw("SUM(CASE WHEN dist.status <> 'dispatched' THEN COALESCE(line.received_quantity, 0) ELSE 0 END) as received_student")
            ->selectRaw("SUM(CASE WHEN dist.status <> 'dispatched' THEN COALESCE(line.received_teacher_guides, 0) ELSE 0 END) as received_teacher")
            ->get();
    }
}
