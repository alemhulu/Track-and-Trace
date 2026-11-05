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

    /**
     * The same for woredas: copies sent into each woreda from outside it, keyed "woreda:book".
     *
     * @param  array<int>|null  $woredaIds
     * @param  array<int>|null  $bookIds
     * @return Collection<string, object>
     */
    public static function inboundWoredaProgress(?array $woredaIds = null, ?array $bookIds = null): Collection
    {
        return static::inbound('woreda', $woredaIds, $bookIds)->keyBy(fn ($row) => $row->woreda_id.':'.$row->manual_book_id);
    }

    /**
     * A title's allocation for the region and, when the zone has its own share, the zone, set
     * against the copies already held there: on hand or on the road inside the place, plus
     * shipments still on their way in from outside it.
     *
     * @return array<int, array{level: string, place_id: int, student: int, teacher: int, held_student: int, held_teacher: int}>
     */
    public static function standing(int $bookId, ?int $regionId, ?int $zoneId, ?string $year = null): array
    {
        $year ??= static::latestYear();
        if (! $year || ! $regionId) {
            return [];
        }

        $levels = ['region' => $regionId] + ($zoneId ? ['zone' => $zoneId] : []);
        $standing = [];

        foreach ($levels as $level => $placeId) {
            $rows = static::query()->where('academic_year', $year)->where('manual_book_id', $bookId)
                ->where("{$level}_id", $placeId)
                ->when($level === 'region', fn (Builder $query) => $query->whereNull('zone_id'))
                ->get(['edition', 'quantity']);

            if ($rows->isEmpty()) {
                continue;
            }

            $stock = DB::table('manual_stock_balances as balance')
                ->join('organizations as place', 'place.id', '=', 'balance.organization_id')
                ->where("place.{$level}_id", $placeId)
                ->where('balance.manual_book_id', $bookId)
                ->groupBy('balance.edition')
                ->selectRaw('balance.edition, SUM(balance.on_hand + balance.in_transit_out) as copies')
                ->pluck('copies', 'edition');
            $arriving = DB::table('manual_distribution_lines as line')
                ->join('manual_distributions as dist', 'dist.id', '=', 'line.manual_distribution_id')
                ->where('dist.status', ManualDistribution::STATUS_DISPATCHED)
                ->where("dist.destination_{$level}_id", $placeId)
                ->where(fn ($inbound) => $inbound->whereNull("dist.{$level}_id")->orWhere("dist.{$level}_id", '!=', $placeId))
                ->where('line.manual_book_id', $bookId)
                ->selectRaw('COALESCE(SUM(line.quantity), 0) as student, COALESCE(SUM(line.teacher_guides), 0) as teacher')
                ->first();

            $standing[] = [
                'level' => ucfirst($level),
                'place_id' => $placeId,
                'student' => (int) $rows->where('edition', ManualBook::EDITION_STUDENT)->sum('quantity'),
                'teacher' => (int) $rows->where('edition', ManualBook::EDITION_TEACHER)->sum('quantity'),
                'held_student' => (int) ($stock[ManualBook::EDITION_STUDENT] ?? 0) + (int) $arriving->student,
                'held_teacher' => (int) ($stock[ManualBook::EDITION_TEACHER] ?? 0) + (int) $arriving->teacher,
            ];
        }

        return $standing;
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
