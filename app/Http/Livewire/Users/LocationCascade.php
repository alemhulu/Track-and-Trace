<?php

namespace App\Http\Livewire\Users;

use App\Models\Organization;
use App\Models\Region;
use App\Models\User;
use App\Models\Woreda;
use App\Models\Zone;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class LocationCascade extends Component
{
    public string $mode = 'create';
    public ?string $accessLevel = null;
    public $countryId = null;
    public $regionId = null;
    public $zoneId = null;
    public $woredaId = null;
    public $organizationId = null;

    public array $accessLevelOptions = [];
    public array $regions = [];
    public array $zones = [];
    public array $woredas = [];
    public array $organizations = [];

    public ?int $actorId = null;
    public ?int $actorCountryId = null;
    public ?int $actorRegionId = null;
    public ?int $actorZoneId = null;
    public ?int $actorWoredaId = null;
    public ?int $actorOrganizationId = null;
    public bool $hadInitialCountry = false;
    public bool $hadInitialRegion = false;

    public function mount(
        string $mode = 'create',
        ?string $initialAccessLevel = null,
        $initialCountryId = null,
        $initialRegionId = null,
        $initialZoneId = null,
        $initialWoredaId = null,
        $initialOrganizationId = null
    ): void {
        $actor = Auth::user();
        if (! $actor instanceof User) {
            abort(403);
        }

        $this->actorId = (int) $actor->id;
        $this->hydrateActorScope();

        $this->mode = in_array($mode, ['create', 'edit'], true) ? $mode : 'create';

        $this->accessLevelOptions = array_values(array_filter(
            config('access_hierarchy.levels', []),
            fn(string $level): bool => $actor->canManageAccessLevel($level)
        ));

        $fallbackLevel = in_array(User::ACCESS_LEVEL_ORGANIZATION, $this->accessLevelOptions, true)
            ? User::ACCESS_LEVEL_ORGANIZATION
            : ($this->accessLevelOptions[0] ?? User::ACCESS_LEVEL_ORGANIZATION);

        $this->accessLevel = in_array((string) $initialAccessLevel, $this->accessLevelOptions, true)
            ? (string) $initialAccessLevel
            : $fallbackLevel;

        $this->countryId = $this->toNullableInt($initialCountryId);
        $this->regionId = $this->toNullableInt($initialRegionId);
        $this->zoneId = $this->toNullableInt($initialZoneId);
        $this->woredaId = $this->toNullableInt($initialWoredaId);
        $this->organizationId = $this->toNullableInt($initialOrganizationId);

        $this->hadInitialCountry = $this->countryId !== null;
        $this->hadInitialRegion = $this->regionId !== null;

        $this->resetHiddenByAccessLevel();
        $this->refreshCascade(false);
    }

    public function updatedAccessLevel(): void
    {
        $this->resetHiddenByAccessLevel();
        $this->refreshCascade(false);
    }

    public function updatedRegionId($value): void
    {
        $this->regionId = $this->toNullableInt($value);
        $this->zoneId = null;
        $this->woredaId = null;
        $this->organizationId = null;

        $region = $this->regionId ? Region::query()->select('id', 'country_id')->find($this->regionId) : null;
        $this->countryId = $region ? (int) $region->country_id : null;

        $this->refreshCascade(true);
    }

    public function updatedZoneId($value): void
    {
        $this->zoneId = $this->toNullableInt($value);
        $this->woredaId = null;
        $this->organizationId = null;

        $zone = $this->zoneId ? Zone::query()->select('id', 'country_id', 'region_id')->find($this->zoneId) : null;
        $this->countryId = $zone ? (int) $zone->country_id : $this->countryId;
        $this->regionId = $zone ? (int) $zone->region_id : $this->regionId;

        $this->refreshCascade(true);
    }

    public function updatedWoredaId($value): void
    {
        $this->woredaId = $this->toNullableInt($value);
        $this->organizationId = null;

        $woreda = $this->woredaId
            ? Woreda::query()
            ->select('id', 'zone_id')
            ->with('zone:id,region_id,country_id')
            ->find($this->woredaId)
            : null;

        $this->zoneId = $woreda ? (int) $woreda->zone_id : $this->zoneId;
        $this->regionId = ($woreda && $woreda->zone) ? (int) $woreda->zone->region_id : $this->regionId;
        $this->countryId = ($woreda && $woreda->zone) ? (int) $woreda->zone->country_id : $this->countryId;

        $this->refreshCascade(true);
    }

    public function updatedOrganizationId($value): void
    {
        $this->organizationId = $this->toNullableInt($value);

        if ($this->accessLevel !== User::ACCESS_LEVEL_ORGANIZATION || ! $this->organizationId) {
            return;
        }

        $organization = Organization::query()
            ->select('id', 'country_id', 'region_id', 'zone_id', 'woreda_id')
            ->find($this->organizationId);

        if (! $organization) {
            return;
        }

        $this->countryId = (int) $organization->country_id;
        $this->regionId = (int) $organization->region_id;
        $this->zoneId = (int) $organization->zone_id;
        $this->woredaId = (int) $organization->woreda_id;

        $this->refreshCascade(true);
    }

    public function showsRegion(): bool
    {
        return in_array($this->accessLevel, [User::ACCESS_LEVEL_REGION, User::ACCESS_LEVEL_ZONE, User::ACCESS_LEVEL_WOREDA, User::ACCESS_LEVEL_ORGANIZATION], true);
    }

    public function showsZone(): bool
    {
        return in_array($this->accessLevel, [User::ACCESS_LEVEL_ZONE, User::ACCESS_LEVEL_WOREDA, User::ACCESS_LEVEL_ORGANIZATION], true);
    }

    public function showsWoreda(): bool
    {
        return in_array($this->accessLevel, [User::ACCESS_LEVEL_WOREDA, User::ACCESS_LEVEL_ORGANIZATION], true);
    }

    public function showsOrganization(): bool
    {
        return $this->accessLevel === User::ACCESS_LEVEL_ORGANIZATION;
    }

    public function render()
    {
        return view('livewire.users.location-cascade');
    }

    private function refreshCascade(bool $preserveChoice): void
    {
        $this->regions = $this->fetchRegions();

        if (! $this->inList($this->regionId, $this->regions)) {
            $this->regionId = null;
        }

        if (! $preserveChoice || $this->accessLevel !== User::ACCESS_LEVEL_NATIONAL) {
            $this->applyLevelDefaults();
        }

        $this->zones = $this->fetchZones();
        if (! $this->inList($this->zoneId, $this->zones)) {
            $this->zoneId = null;
        }

        $this->woredas = $this->fetchWoredas();
        if (! $this->inList($this->woredaId, $this->woredas)) {
            $this->woredaId = null;
        }

        $this->organizations = $this->fetchOrganizations();
        if (! $this->inList($this->organizationId, $this->organizations)) {
            $this->organizationId = null;
        }

        if ($this->organizationId && $this->accessLevel === User::ACCESS_LEVEL_ORGANIZATION) {
            $organization = Organization::query()
                ->select('id', 'country_id', 'region_id', 'zone_id', 'woreda_id')
                ->find($this->organizationId);

            if ($organization) {
                $this->countryId = (int) $organization->country_id;
                $this->regionId = (int) $organization->region_id;
                $this->zoneId = (int) $organization->zone_id;
                $this->woredaId = (int) $organization->woreda_id;
            }
        }

        $this->syncCountryFromSelection();
    }

    private function applyLevelDefaults(): void
    {
        if ($this->mode === 'create') {
            if ($this->accessLevel === User::ACCESS_LEVEL_NATIONAL) {
                return;
            }

            if ($this->accessLevel === User::ACCESS_LEVEL_ORGANIZATION) {
                if (! $this->regionId && $this->hadInitialRegion) {
                    $this->regionId = $this->firstId($this->regions);
                }

                return;
            }

            return;
        }

        if ($this->accessLevel === User::ACCESS_LEVEL_NATIONAL) {
            return;
        }

        if (in_array($this->accessLevel, [User::ACCESS_LEVEL_REGION, User::ACCESS_LEVEL_ZONE, User::ACCESS_LEVEL_WOREDA, User::ACCESS_LEVEL_ORGANIZATION], true)) {
            if (! $this->regionId) {
                $this->regionId = $this->actorRegionId ?: $this->firstId($this->regions);
            }
        }

        // Keep dependent selections empty until explicitly chosen by the user.
        // This prevents zone/woreda/organization from being auto-selected when only region is selected.
        if (in_array($this->accessLevel, [User::ACCESS_LEVEL_ZONE, User::ACCESS_LEVEL_WOREDA, User::ACCESS_LEVEL_ORGANIZATION], true) && ! $this->zoneId) {
            $this->zoneId = null;
        }

        if (in_array($this->accessLevel, [User::ACCESS_LEVEL_WOREDA, User::ACCESS_LEVEL_ORGANIZATION], true) && ! $this->woredaId) {
            $this->woredaId = null;
        }

        if ($this->accessLevel === User::ACCESS_LEVEL_ORGANIZATION && ! $this->organizationId) {
            $this->organizationId = null;
        }
    }

    private function resetHiddenByAccessLevel(): void
    {
        if ($this->accessLevel === User::ACCESS_LEVEL_NATIONAL) {
            $this->regionId = null;
            $this->zoneId = null;
            $this->woredaId = null;
            $this->organizationId = null;
            return;
        }

        if ($this->accessLevel === User::ACCESS_LEVEL_REGION) {
            $this->zoneId = null;
            $this->woredaId = null;
            $this->organizationId = null;
            return;
        }

        if ($this->accessLevel === User::ACCESS_LEVEL_ZONE) {
            $this->woredaId = null;
            $this->organizationId = null;
            return;
        }

        if ($this->accessLevel === User::ACCESS_LEVEL_WOREDA) {
            $this->organizationId = null;
        }
    }

    private function fetchRegions(): array
    {
        $query = Region::query()->select('id', 'name', 'country_id')->orderBy('name');

        if ($this->actorCountryId) {
            $query->where('country_id', $this->actorCountryId);
        }

        if ($this->actorRegionId) {
            $query->where('id', $this->actorRegionId);
        }

        return $query->get()->map(fn(Region $region): array => [
            'id' => (int) $region->id,
            'name' => $region->name,
            'country_id' => (int) $region->country_id,
        ])->all();
    }

    private function fetchZones(): array
    {
        $query = Zone::query()->select('id', 'name', 'country_id', 'region_id')->orderBy('name');

        if ($this->actorCountryId) {
            $query->where('country_id', $this->actorCountryId);
        }

        if ($this->actorRegionId) {
            $query->where('region_id', $this->actorRegionId);
        }

        if ($this->actorZoneId) {
            $query->where('id', $this->actorZoneId);
        }

        if ($this->regionId) {
            $query->where('region_id', $this->regionId);
        }

        return $query->get()->map(fn(Zone $zone): array => [
            'id' => (int) $zone->id,
            'name' => $zone->name,
            'region_id' => (int) $zone->region_id,
            'country_id' => (int) $zone->country_id,
        ])->all();
    }

    private function fetchWoredas(): array
    {
        $query = Woreda::query()
            ->select('id', 'name', 'zone_id')
            ->with('zone:id,region_id,country_id')
            ->orderBy('name');

        if ($this->actorCountryId) {
            $query->whereHas('zone', function ($zoneQuery): void {
                $zoneQuery->where('country_id', $this->actorCountryId);
            });
        }

        if ($this->actorRegionId) {
            $query->whereHas('zone', function ($zoneQuery): void {
                $zoneQuery->where('region_id', $this->actorRegionId);
            });
        }

        if ($this->actorZoneId) {
            $query->where('zone_id', $this->actorZoneId);
        }

        if ($this->actorWoredaId) {
            $query->where('id', $this->actorWoredaId);
        }

        if ($this->regionId) {
            $query->whereHas('zone', function ($zoneQuery): void {
                $zoneQuery->where('region_id', $this->regionId);
            });
        }

        if ($this->zoneId) {
            $query->where('zone_id', $this->zoneId);
        }

        return $query->get()->map(fn(Woreda $woreda): array => [
            'id' => (int) $woreda->id,
            'name' => $woreda->name,
            'zone_id' => (int) $woreda->zone_id,
            'region_id' => $woreda->zone ? (int) $woreda->zone->region_id : null,
            'country_id' => $woreda->zone ? (int) $woreda->zone->country_id : null,
        ])->all();
    }

    private function fetchOrganizations(): array
    {
        $query = Organization::query()
            ->select('id', 'name', 'country_id', 'region_id', 'zone_id', 'woreda_id')
            ->accessibleBy($this->currentActor())
            ->orderBy('name');

        if ($this->regionId) {
            $query->where('region_id', $this->regionId);
        }

        if ($this->zoneId) {
            $query->where('zone_id', $this->zoneId);
        }

        if ($this->woredaId) {
            $query->where('woreda_id', $this->woredaId);
        }

        return $query->get()->map(fn(Organization $organization): array => [
            'id' => (int) $organization->id,
            'name' => $organization->name,
            'country_id' => (int) $organization->country_id,
            'region_id' => (int) $organization->region_id,
            'zone_id' => (int) $organization->zone_id,
            'woreda_id' => (int) $organization->woreda_id,
        ])->all();
    }

    private function syncCountryFromSelection(): void
    {
        if ($this->woredaId) {
            $woreda = Woreda::query()
                ->select('id', 'zone_id')
                ->with('zone:id,country_id')
                ->find($this->woredaId);

            if ($woreda && $woreda->zone) {
                $this->countryId = (int) $woreda->zone->country_id;
                return;
            }
        }

        if ($this->zoneId) {
            $zone = Zone::query()->select('id', 'country_id')->find($this->zoneId);
            if ($zone) {
                $this->countryId = (int) $zone->country_id;
                return;
            }
        }

        if ($this->regionId) {
            $region = Region::query()->select('id', 'country_id')->find($this->regionId);
            if ($region) {
                $this->countryId = (int) $region->country_id;
                return;
            }
        }

        if ($this->organizationId) {
            $organization = Organization::query()->select('id', 'country_id')->find($this->organizationId);
            if ($organization) {
                $this->countryId = (int) $organization->country_id;
                return;
            }
        }

        if ($this->mode === 'create' && ! $this->hadInitialCountry) {
            $this->countryId = null;
            return;
        }

        $this->countryId = $this->actorCountryId;
    }

    private function inList($id, array $list): bool
    {
        if (! $id) {
            return true;
        }

        foreach ($list as $item) {
            if ((int) $item['id'] === (int) $id) {
                return true;
            }
        }

        return false;
    }

    private function firstId(array $list): ?int
    {
        return ! empty($list) ? (int) $list[0]['id'] : null;
    }

    private function toNullableInt($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    private function hydrateActorScope(): void
    {
        $actor = $this->currentActor();

        $this->actorCountryId = $this->toNullableInt($actor->country_id);
        $this->actorRegionId = $this->toNullableInt($actor->region_id);
        $this->actorZoneId = $this->toNullableInt($actor->zone_id);
        $this->actorWoredaId = $this->toNullableInt($actor->woreda_id);
        $this->actorOrganizationId = $this->toNullableInt($actor->organization_id);

        if ($this->actorOrganizationId) {
            $organization = Organization::query()
                ->select('id', 'country_id', 'region_id', 'zone_id', 'woreda_id')
                ->find($this->actorOrganizationId);

            if ($organization) {
                $this->actorCountryId = $this->actorCountryId ?: (int) $organization->country_id;
                $this->actorRegionId = $this->actorRegionId ?: (int) $organization->region_id;
                $this->actorZoneId = $this->actorZoneId ?: (int) $organization->zone_id;
                $this->actorWoredaId = $this->actorWoredaId ?: (int) $organization->woreda_id;
            }
        }
    }

    private function currentActor(): User
    {
        $actor = Auth::user();

        if ($actor instanceof User) {
            return $actor;
        }

        if ($this->actorId) {
            $actorById = User::query()->find($this->actorId);
            if ($actorById) {
                return $actorById;
            }
        }

        abort(403);
    }
}
