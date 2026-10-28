<div class="grid gap-5 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <x-label for="access_level" value="Access level" />
        <div class="mt-1.5">
            <select wire:model="accessLevel" name="access_level" id="access_level"
                class="form-control" required>
                @foreach($accessLevelOptions as $level)
                <option value="{{ $level }}">{{ ucfirst($level) }}</option>
                @endforeach
            </select>
            <p class="form-hint">
                @if($accessLevel === \App\Models\User::ACCESS_LEVEL_NATIONAL)
                National access selected: all location and organization selectors are hidden.
                @elseif($accessLevel === \App\Models\User::ACCESS_LEVEL_REGION)
                Region access selected: only Region selector is visible.
                @elseif($accessLevel === \App\Models\User::ACCESS_LEVEL_ZONE)
                Zone access selected: Region and Zone selectors are visible.
                @elseif($accessLevel === \App\Models\User::ACCESS_LEVEL_WOREDA)
                Woreda access selected: Region, Zone, and Woreda selectors are visible.
                @elseif($accessLevel === \App\Models\User::ACCESS_LEVEL_ORGANIZATION)
                Organization access selected: Region, Zone, Woreda, and Organization selectors are visible.
                @endif
            </p>
            <x-input-error for="access_level" />
        </div>
    </div>

    @if ($this->showsRegion())
    <div wire:key="region-row" id="region-row">
        <x-label for="region_id" value="Region" />
        <div class="mt-1.5">
            <select wire:model="regionId" id="region_id" class="form-control"
                @if ($this->isLocked('region_id')) disabled title="Set by your own access level" @else name="region_id" @endif>
                <option value="">Select Region</option>
                @foreach($regions as $region)
                <option value="{{ $region['id'] }}">{{ $region['name'] }}</option>
                @endforeach
            </select>
            @if ($this->isLocked('region_id'))
            <input type="hidden" name="region_id" value="{{ $regionId }}">
            @endif
            <x-input-error for="region_id" />
        </div>
    </div>
    @endif

    @if ($this->showsZone())
    <div wire:key="zone-row" id="zone-row">
        <x-label for="zone_id" value="Zone" />
        <div class="mt-1.5">
            <select wire:model="zoneId" id="zone_id" class="form-control"
                @if ($this->isLocked('zone_id')) disabled title="Set by your own access level" @else name="zone_id" @endif>
                <option value="">Select Zone</option>
                @foreach($zones as $zone)
                <option value="{{ $zone['id'] }}">{{ $zone['name'] }}</option>
                @endforeach
            </select>
            @if ($this->isLocked('zone_id'))
            <input type="hidden" name="zone_id" value="{{ $zoneId }}">
            @endif
            <x-input-error for="zone_id" />
        </div>
    </div>
    @endif

    @if ($this->showsWoreda())
    <div wire:key="woreda-row" id="woreda-row">
        <x-label for="woreda_id" value="Woreda" />
        <div class="mt-1.5">
            <select wire:model="woredaId" id="woreda_id" class="form-control"
                @if ($this->isLocked('woreda_id')) disabled title="Set by your own access level" @else name="woreda_id" @endif>
                <option value="">Select Woreda</option>
                @foreach($woredas as $woreda)
                <option value="{{ $woreda['id'] }}">{{ $woreda['name'] }}</option>
                @endforeach
            </select>
            @if ($this->isLocked('woreda_id'))
            <input type="hidden" name="woreda_id" value="{{ $woredaId }}">
            @endif
            <x-input-error for="woreda_id" />
        </div>
    </div>
    @endif

    @if ($this->showsOrganization())
    <div wire:key="organization-row" id="organization-row">
        <x-label for="organization_id" value="Organization" />
        <div class="mt-1.5">
            <select wire:model="organizationId" id="organization_id"
                class="form-control"
                @if ($this->isLocked('organization_id')) disabled title="Set by your own access level" @else name="organization_id" @endif>
                <option value="">Select Organization</option>
                @foreach($organizations as $organization)
                <option value="{{ $organization['id'] }}">{{ $organization['name'] }}</option>
                @endforeach
            </select>
            @if ($this->isLocked('organization_id'))
            <input type="hidden" name="organization_id" value="{{ $organizationId }}">
            @endif
            <x-input-error for="organization_id" />
        </div>
    </div>
    @endif

    <input type="hidden" wire:model="countryId" name="country_id" value="{{ $countryId ?? '' }}">
</div>
