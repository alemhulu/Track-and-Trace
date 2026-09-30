<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
            {{ __('Users Management') }}
        </h2>
    </x-slot>

    <x-slot name="actionButton">
        <a href="{{ route('users.index') }}">
            <x-button class="flex ">
                <i class="flex mr-2 fi-rr-arrow-left"></i>
                {{ __('Back') }}
            </x-button>
        </a>
    </x-slot>

    <x-form-card action="{{ route('users.store') }}" title="Create New User">
        <x-slot name="body">
            <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:border-gray-200 sm:pt-5">
                <x-label for="name" value="Name" />
                <div class="mt-1 sm:mt-0 sm:col-span-2">
                    <x-input type="text" name="name" id="name" value="{{ old('name') }}" />
                    <x-input-error for="name" />
                </div>
            </div>

            <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:border-gray-200 sm:pt-5">
                <x-label for="email" value="Email" />
                <div class="mt-1 sm:mt-0 sm:col-span-2">
                    <x-input type="text" name="email" id="email" value="{{ old('email') }}" />
                    <x-input-error for="email" />
                </div>
            </div>

            <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:border-gray-200 sm:pt-5">
                <x-label for="access_level" value="Access Level" />
                <div class="mt-1 sm:mt-0 sm:col-span-2">
                    <select name="access_level" id="access_level" class="w-full border-gray-300 rounded-md" required>
                        @foreach($accessLevels as $level)
                        <option value="{{ $level }}" @selected(old('access_level', 'organization' )===$level)>
                            {{ ucfirst($level) }}
                        </option>
                        @endforeach
                    </select>
                    <x-input-error for="access_level" />
                </div>
            </div>

            <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:border-gray-200 sm:pt-5"
                id="organization-row">
                <x-label for="organization_id" value="Organization" />
                <div class="mt-1 sm:mt-0 sm:col-span-2">
                    <select name="organization_id" id="organization_id" class="w-full border-gray-300 rounded-md">
                        <option value="">Select Organization</option>
                        @foreach($organizations as $organization)
                        <option value="{{ $organization->id }}" data-country-id="{{ $organization->country_id }}"
                            data-region-id="{{ $organization->region_id }}" data-zone-id="{{ $organization->zone_id }}"
                            data-woreda-id="{{ $organization->woreda_id }}" @selected((string)
                            old('organization_id')===(string) $organization->id)
                            >
                            {{ $organization->name }}
                        </option>
                        @endforeach
                    </select>
                    <x-input-error for="organization_id" />
                </div>
            </div>

            <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:border-gray-200 sm:pt-5" id="country-row">
                <x-label for="country_id" value="Country" />
                <div class="mt-1 sm:mt-0 sm:col-span-2">
                    <select name="country_id" id="country_id" class="w-full border-gray-300 rounded-md">
                        <option value="">Select Country</option>
                        @foreach($countries as $country)
                        <option value="{{ $country->id }}" @selected((string) old('country_id')===(string) $country->
                            id)>{{ $country->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error for="country_id" />
                </div>
            </div>

            <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:border-gray-200 sm:pt-5" id="region-row">
                <x-label for="region_id" value="Region" />
                <div class="mt-1 sm:mt-0 sm:col-span-2">
                    <select name="region_id" id="region_id" class="w-full border-gray-300 rounded-md">
                        <option value="">Select Region</option>
                        @foreach($regions as $region)
                        <option value="{{ $region->id }}" data-country-id="{{ $region->country_id }}" @selected((string)
                            old('region_id')===(string) $region->id)>{{ $region->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error for="region_id" />
                </div>
            </div>

            <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:border-gray-200 sm:pt-5" id="zone-row">
                <x-label for="zone_id" value="Zone" />
                <div class="mt-1 sm:mt-0 sm:col-span-2">
                    <select name="zone_id" id="zone_id" class="w-full border-gray-300 rounded-md">
                        <option value="">Select Zone</option>
                        @foreach($zones as $zone)
                        <option value="{{ $zone->id }}" data-region-id="{{ $zone->region_id }}" @selected((string)
                            old('zone_id')===(string) $zone->id)>{{ $zone->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error for="zone_id" />
                </div>
            </div>

            <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:border-gray-200 sm:pt-5" id="woreda-row">
                <x-label for="woreda_id" value="Woreda" />
                <div class="mt-1 sm:mt-0 sm:col-span-2">
                    <select name="woreda_id" id="woreda_id" class="w-full border-gray-300 rounded-md">
                        <option value="">Select Woreda</option>
                        @foreach($woredas as $woreda)
                        <option value="{{ $woreda->id }}" data-zone-id="{{ $woreda->zone_id }}" @selected((string)
                            old('woreda_id')===(string) $woreda->id)>{{ $woreda->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error for="woreda_id" />
                </div>
            </div>

            <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:border-gray-200 sm:pt-5">
                <x-label for="password" value="Password" />
                <div class="mt-1 sm:mt-0 sm:col-span-2">
                    <x-input type="password" name="password" id="password" />
                    <x-input-error for="password" />
                </div>
            </div>

            <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:border-gray-200 sm:pt-5">
                <x-label for="confirm-password" value="Password Confirmation" />
                <div class="mt-1 sm:mt-0 sm:col-span-2">
                    <x-input type="password" name="confirm-password" id="confirm-password" />
                    <x-input-error for="confirm-password" />
                </div>
            </div>

            <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:border-gray-200 sm:pt-5">
                <x-label for="title" value="Assign Roles" />
                <div class="px-3 pt-1 pb-3 mt-1 border border-gray-300 rounded-md sm:col-span-2">
                    <p class="text-xs font-semibold text-gray-400">select one or more</p>
                    <div class="gap-2 sm:grid sm:grid-cols-4">
                        @foreach ($roles as $role)
                        <x-multi-checkbox name="roles[]" value="{{ $role }}" title="{{ $role }}"
                            checked="{{ in_array($role, old('roles', []), true) ? true : false }}" />
                        @endforeach
                    </div>
                    <x-input-error for="roles" />
                </div>
            </div>
        </x-slot>
    </x-form-card>

    <script>
        (() => {
            const accessLevel = document.getElementById('access_level');
            const organization = document.getElementById('organization_id');
            const country = document.getElementById('country_id');
            const region = document.getElementById('region_id');
            const zone = document.getElementById('zone_id');
            const woreda = document.getElementById('woreda_id');

            const rows = {
                organization: document.getElementById('organization-row'),
                country: document.getElementById('country-row'),
                region: document.getElementById('region-row'),
                zone: document.getElementById('zone-row'),
                woreda: document.getElementById('woreda-row'),
            };

            const syncVisibility = () => {
                const level = accessLevel.value;

                rows.organization.classList.toggle('hidden', level !== 'organization');
                rows.country.classList.toggle('hidden', level === 'national');
                rows.region.classList.toggle('hidden', !['region', 'zone', 'woreda'].includes(level));
                rows.zone.classList.toggle('hidden', !['zone', 'woreda'].includes(level));
                rows.woreda.classList.toggle('hidden', level !== 'woreda');
            };

            const filterOptions = (select, attr, value) => {
                Array.from(select.options).forEach((option, index) => {
                    if (index === 0) {
                        option.hidden = false;
                        return;
                    }

                    if (!value) {
                        option.hidden = false;
                        return;
                    }

                    option.hidden = String(option.dataset[attr]) !== String(value);
                });
            };

            const syncCascades = () => {
                filterOptions(region, 'countryId', country.value);
                filterOptions(zone, 'regionId', region.value);
                filterOptions(woreda, 'zoneId', zone.value);
            };

            const hydrateFromOrganization = () => {
                if (accessLevel.value !== 'organization') {
                    return;
                }

                const selected = organization.selectedOptions[0];
                if (!selected || !selected.value) {
                    return;
                }

                country.value = selected.dataset.countryId || '';
                syncCascades();
                region.value = selected.dataset.regionId || '';
                syncCascades();
                zone.value = selected.dataset.zoneId || '';
                syncCascades();
                woreda.value = selected.dataset.woredaId || '';
            };

            accessLevel.addEventListener('change', () => {
                syncVisibility();
                hydrateFromOrganization();
            });

            country.addEventListener('change', () => {
                region.value = '';
                zone.value = '';
                woreda.value = '';
                syncCascades();
            });

            region.addEventListener('change', () => {
                zone.value = '';
                woreda.value = '';
                syncCascades();
            });

            zone.addEventListener('change', () => {
                woreda.value = '';
                syncCascades();
            });

            organization.addEventListener('change', hydrateFromOrganization);

            syncVisibility();
            syncCascades();
            hydrateFromOrganization();
        })();
    </script>
</x-app-layout>