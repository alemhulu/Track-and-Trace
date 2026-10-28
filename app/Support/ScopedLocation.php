<?php

namespace App\Support;

use App\Models\Organization;
use App\Models\Region;
use App\Models\User;
use App\Models\Woreda;
use App\Models\Zone;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Region → zone → woreda → organization choices for one user. The levels the user's access
 * level pins are filled in and locked, and every list is cut to the user's area.
 */
class ScopedLocation
{
    public const AREA = ['region_id', 'zone_id', 'woreda_id'];

    private const LOCKED_BY_LEVEL = [
        User::ACCESS_LEVEL_NATIONAL => [],
        User::ACCESS_LEVEL_REGION => ['region_id'],
        User::ACCESS_LEVEL_ZONE => ['region_id', 'zone_id'],
        User::ACCESS_LEVEL_WOREDA => ['region_id', 'zone_id', 'woreda_id'],
        User::ACCESS_LEVEL_ORGANIZATION => ['region_id', 'zone_id', 'woreda_id', 'organization_id'],
    ];

    private function __construct(private ?User $actor, private bool $lockOrganization = true)
    {
    }

    public static function for(User $actor): self
    {
        return new self($actor);
    }

    // For choosing a counterpart rather than the user's own place: the organization stays free
    public static function destinationFor(User $actor): self
    {
        return new self($actor, false);
    }

    // Nothing locked or cut, for printers and hubs that deliver anywhere
    public static function open(): self
    {
        return new self(null);
    }

    /**
     * The fixed value of each locked level, keyed by level.
     *
     * @return array<string, string>
     */
    public function locked(): array
    {
        if (! $this->actor || $this->actor->hasNationalAccess()) {
            return [];
        }

        $level = $this->actor->effectiveAccessLevel();
        $place = $level === User::ACCESS_LEVEL_ORGANIZATION ? $this->actor->organization : $this->actor;
        $values = [
            'region_id' => $place?->region_id,
            'zone_id' => $place?->zone_id,
            'woreda_id' => $place?->woreda_id,
            'organization_id' => $this->actor->organization_id,
        ];

        $keys = self::LOCKED_BY_LEVEL[$level] ?? [];
        if (! $this->lockOrganization) {
            $keys = array_diff($keys, ['organization_id']);
        }

        return collect($keys)->mapWithKeys(fn ($key) => [$key => (string) ($values[$key] ?? '')])->all();
    }

    public function isLocked(string $key): bool
    {
        return array_key_exists($key, $this->locked());
    }

    /**
     * Puts the locks back, clears levels below the one just changed, and drops any value
     * that is not one of the choices.
     *
     * @param  array{region_id?: mixed, zone_id?: mixed, woreda_id?: mixed}  $area
     * @return array{region_id: string, zone_id: string, woreda_id: string}
     */
    public function apply(array $area, ?string $changed = null): array
    {
        $locked = $this->locked();
        $clearing = false;
        $clean = [];

        foreach (self::AREA as $key) {
            $value = $clearing ? '' : (string) ($area[$key] ?? '');
            $clean[$key] = $locked[$key] ?? $value;
            $clearing = $clearing || $key === $changed;
        }

        foreach (['zone_id' => 'zones', 'woreda_id' => 'woredas'] as $key => $list) {
            if ($clean[$key] !== '' && ! array_key_exists($key, $locked) && ! $this->{$list}($clean)->contains('id', (int) $clean[$key])) {
                $clean[$key] = '';
            }
        }

        return $clean;
    }

    // The organization to show: the locked one, or the chosen one if it is inside the area
    public function organization(array $area, $organizationId): string
    {
        $locked = $this->locked();
        if (array_key_exists('organization_id', $locked)) {
            return $locked['organization_id'];
        }

        return $organizationId && $this->organizations($area)->whereKey($organizationId)->exists() ? (string) $organizationId : '';
    }

    public function regions(): Collection
    {
        return Region::query()
            ->when($this->actor, fn ($query) => $query->accessibleBy($this->actor))
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function zones(array $area): Collection
    {
        if (empty($area['region_id'])) {
            return collect();
        }

        return Zone::query()
            ->when($this->actor, fn ($query) => $query->accessibleBy($this->actor))
            ->where('region_id', $area['region_id'])
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function woredas(array $area): Collection
    {
        if (empty($area['zone_id'])) {
            return collect();
        }

        return Woreda::query()
            ->when($this->actor, fn ($query) => $query->accessibleBy($this->actor))
            ->where('zone_id', $area['zone_id'])
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function organizations(array $area): Builder
    {
        return Organization::query()
            ->when($this->actor, fn ($query) => $query->accessibleBy($this->actor))
            ->when($area['region_id'] ?? null, fn ($query, $id) => $query->where('region_id', $id))
            ->when($area['zone_id'] ?? null, fn ($query, $id) => $query->where('zone_id', $id))
            ->when($area['woreda_id'] ?? null, fn ($query, $id) => $query->where('woreda_id', $id));
    }

    /**
     * Everything the location picker component needs.
     */
    public function picker(array $area, ?Builder $organizations = null): array
    {
        return [
            'area' => $area,
            'locked' => $this->locked(),
            'regions' => $this->regions(),
            'zones' => $this->zones($area),
            'woredas' => $this->woredas($area),
            'organizations' => ($organizations ?? $this->organizations($area))->orderBy('name')->get(['id', 'name']),
        ];
    }
}
