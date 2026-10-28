<div x-data class="grid gap-5 sm:grid-cols-2">
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

    <div x-show="{{ $this->showsRegion() ? 'true' : 'false' }}" @unless($this->showsRegion()) style="display: none" @endunless id="region-row">
        <x-label for="region_id" value="Region" />
        <div class="mt-1.5">
            <select wire:model="regionId" name="region_id" id="region_id" class="form-control">
                <option value="">Select Region</option>
                @foreach($regions as $region)
                <option value="{{ $region['id'] }}">{{ $region['name'] }}</option>
                @endforeach
            </select>
            <x-input-error for="region_id" />
        </div>
    </div>

    <div x-show="{{ $this->showsZone() ? 'true' : 'false' }}" @unless($this->showsZone()) style="display: none" @endunless id="zone-row">
        <x-label for="zone_id" value="Zone" />
        <div class="mt-1.5">
            <select wire:model="zoneId" name="zone_id" id="zone_id" class="form-control">
                <option value="">Select Zone</option>
                @foreach($zones as $zone)
                <option value="{{ $zone['id'] }}">{{ $zone['name'] }}</option>
                @endforeach
            </select>
            <x-input-error for="zone_id" />
        </div>
    </div>

    <div x-show="{{ $this->showsWoreda() ? 'true' : 'false' }}" @unless($this->showsWoreda()) style="display: none" @endunless id="woreda-row">
        <x-label for="woreda_id" value="Woreda" />
        <div class="mt-1.5">
            <select wire:model="woredaId" name="woreda_id" id="woreda_id" class="form-control">
                <option value="">Select Woreda</option>
                @foreach($woredas as $woreda)
                <option value="{{ $woreda['id'] }}">{{ $woreda['name'] }}</option>
                @endforeach
            </select>
            <x-input-error for="woreda_id" />
        </div>
    </div>

    <div x-show="{{ $this->showsOrganization() ? 'true' : 'false' }}" @unless($this->showsOrganization()) style="display: none" @endunless id="organization-row">
        <x-label for="organization_id" value="Organization" />
        <div class="mt-1.5">
            <select wire:model="organizationId" name="organization_id" id="organization_id"
                class="form-control">
                <option value="">Select Organization</option>
                @foreach($organizations as $organization)
                <option value="{{ $organization['id'] }}">{{ $organization['name'] }}</option>
                @endforeach
            </select>
            <x-input-error for="organization_id" />
        </div>
    </div>

    <input type="hidden" wire:model="countryId" name="country_id" value="{{ $countryId ?? '' }}">
</div>
