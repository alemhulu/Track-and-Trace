<div x-data="{ show: true }">
    <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:border-gray-200 sm:pt-5">
        <x-label for="access_level" value="Access Level" />
        <div class="mt-1 sm:mt-0 sm:col-span-2">
            <select wire:model="accessLevel" name="access_level" id="access_level"
                class="w-full border-gray-300 rounded-md" required>
                @foreach($accessLevelOptions as $level)
                <option value="{{ $level }}">{{ ucfirst($level) }}</option>
                @endforeach
            </select>
            <p class="mt-1 text-xs text-gray-500">
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

    <div x-show="{{ $this->showsRegion() ? 'true' : 'false' }}"
        class="hidden sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:border-gray-200 sm:pt-5" id="region-row">
        <x-label for="region_id" value="Region" />
        <div class="mt-1 sm:mt-0 sm:col-span-2">
            <select wire:model="regionId" name="region_id" id="region_id" class="w-full border-gray-300 rounded-md">
                <option value="">Select Region</option>
                @foreach($regions as $region)
                <option value="{{ $region['id'] }}">{{ $region['name'] }}</option>
                @endforeach
            </select>
            <x-input-error for="region_id" />
        </div>
    </div>

    <div x-show="{{ $this->showsZone() ? 'true' : 'false' }}"
        class="hidden  sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:border-gray-200 sm:pt-5" id="zone-row">
        <x-label for="zone_id" value="Zone" />
        <div class="mt-1 sm:mt-0 sm:col-span-2">
            <select wire:model="zoneId" name="zone_id" id="zone_id" class="w-full border-gray-300 rounded-md">
                <option value="">Select Zone</option>
                @foreach($zones as $zone)
                <option value="{{ $zone['id'] }}">{{ $zone['name'] }}</option>
                @endforeach
            </select>
            <x-input-error for="zone_id" />
        </div>
    </div>

    <div x-show="{{ $this->showsWoreda() ? 'true' : 'false' }}"
        class="hidden  sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:border-gray-200 sm:pt-5" id="woreda-row">
        <x-label for="woreda_id" value="Woreda" />
        <div class="mt-1 sm:mt-0 sm:col-span-2">
            <select wire:model="woredaId" name="woreda_id" id="woreda_id" class="w-full border-gray-300 rounded-md">
                <option value="">Select Woreda</option>
                @foreach($woredas as $woreda)
                <option value="{{ $woreda['id'] }}">{{ $woreda['name'] }}</option>
                @endforeach
            </select>
            <x-input-error for="woreda_id" />
        </div>
    </div>

    <div x-show="{{ $this->showsOrganization() ? 'true' : 'false' }}"
        class="hidden  sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:border-gray-200 sm:pt-5" id="organization-row">
        <x-label for="organization_id" value="Organization" />
        <div class="mt-1 sm:mt-0 sm:col-span-2">
            <select wire:model="organizationId" name="organization_id" id="organization_id"
                class="w-full border-gray-300 rounded-md">
                <option value="">Select Organization</option>
                @foreach($organizations as $organization)
                <option value="{{ $organization['id'] }}">{{ $organization['name'] }}</option>
                @endforeach
            </select>
            <x-input-error for="organization_id" />
        </div>
    </div>

    <input type="hidden" name="country_id" value="{{ $countryId ?? '' }}">
</div>
