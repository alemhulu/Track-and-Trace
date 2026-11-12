<?php

namespace App\Services\ManualTracking;

use App\Models\Region;
use App\Models\Zone;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Lines a database's regions and zones up with the printer's, from a reviewed map, so the
 * printer import finds them instead of creating duplicates.
 *
 * The map (JSON) has three parts, each applied only when the database still looks the way the
 * map expects, so a stale or mistaken map stops instead of renaming the wrong rows:
 *
 * - regions: [{id, from, to}]                  rename region `id` from `from` to `to`
 * - move_zones: [{to_region, zones: [ids]}]     move zones, with everything in them, to a region
 * - zone_codes: [{zone, from_name, code}]       give zone `zone` (named `from_name`) the printer's code
 *
 * Running it again after it was applied changes nothing.
 */
class PlaceAlignment
{
    /**
     * @return array{changes: list<string>, errors: list<string>}
     */
    public function apply(array $map, bool $commit): array
    {
        $report = ['changes' => [], 'errors' => []];

        $run = function () use ($map, &$report): void {
            $this->renameRegions($map['regions'] ?? [], $report);
            $this->moveZones($map['move_zones'] ?? [], $report);
            $this->codeZones($map['zone_codes'] ?? [], $report);
        };

        DB::beginTransaction();
        try {
            $run();
            $commit && ! $report['errors'] ? DB::commit() : DB::rollBack();
        } catch (\Throwable $exception) {
            DB::rollBack();
            throw $exception;
        }

        return $report;
    }

    private function renameRegions(array $renames, array &$report): void
    {
        foreach ($renames as $rename) {
            $region = Region::query()->find($rename['id']);
            $current = $region ? trim($region->name) : null;

            if ($current === $rename['to']) {
                continue;
            }
            if ($current !== $rename['from']) {
                $report['errors'][] = sprintf('Region %d is "%s", not "%s"; it was not renamed.', $rename['id'], $current ?? 'missing', $rename['from']);

                continue;
            }

            $region->update(['name' => $rename['to']]);
            $report['changes'][] = sprintf('Region %d: "%s" → "%s"', $region->id, $rename['from'], $rename['to']);
        }
    }

    private function moveZones(array $moves, array &$report): void
    {
        // Every table that places a row in a zone also names its region; all of them move together
        $tables = collect(Schema::getAllTables())
            ->map(fn ($table) => array_values((array) $table)[0])
            ->filter(fn ($table) => Schema::hasColumns($table, ['region_id', 'zone_id']))
            ->values();

        foreach ($moves as $move) {
            $target = Region::query()->find($move['to_region']);
            if (! $target) {
                $report['errors'][] = "Region {$move['to_region']} does not exist; no zone was moved there.";

                continue;
            }

            $zones = Zone::query()->whereKey($move['zones'])->get();
            if ($zones->count() !== count($move['zones'])) {
                $report['errors'][] = 'Zones ' . implode(', ', array_diff($move['zones'], $zones->modelKeys())) . " do not exist; nothing was moved to {$target->name}.";

                continue;
            }

            $moving = $zones->where('region_id', '!=', $target->id)->modelKeys();
            if (! $moving) {
                continue;
            }

            Zone::query()->whereKey($moving)->update(['region_id' => $target->id]);
            $counts = $tables->mapWithKeys(fn ($table) => [$table => DB::table($table)->whereIn('zone_id', $moving)->update(['region_id' => $target->id])])
                ->filter();

            $report['changes'][] = sprintf('%d zones moved to %s (%s)', count($moving), $target->name,
                $counts->map(fn ($count, $table) => number_format($count) . " {$table}")->implode(', ') ?: 'nothing in them');
        }
    }

    private function codeZones(array $codes, array &$report): void
    {
        foreach ($codes as $entry) {
            $zone = Zone::query()->find($entry['zone']);
            if (! $zone || trim($zone->name) !== trim($entry['from_name'])) {
                $report['errors'][] = sprintf('Zone %d is "%s", not "%s"; its code was not set.', $entry['zone'], $zone ? trim($zone->name) : 'missing', $entry['from_name']);

                continue;
            }
            if ($zone->code === $entry['code']) {
                continue;
            }

            $taken = Zone::query()->where('region_id', $zone->region_id)->where('code', $entry['code'])->whereKeyNot($zone->id)->value('name');
            if ($taken) {
                $report['errors'][] = "Code {$entry['code']} already belongs to zone \"{$taken}\"; \"{$zone->name}\" kept its code.";

                continue;
            }

            $report['changes'][] = sprintf('Zone %d "%s": code %s → %s', $zone->id, trim($zone->name), $zone->code ?? '—', $entry['code']);
            $zone->update(['code' => $entry['code']]);
        }
    }
}
