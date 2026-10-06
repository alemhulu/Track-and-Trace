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

            @livewire('users.location-cascade', [
            'mode' => 'create',
            'initialAccessLevel' => old('access_level', 'organization'),
            'initialCountryId' => old('country_id'),
            'initialRegionId' => old('region_id'),
            'initialZoneId' => old('zone_id'),
            'initialWoredaId' => old('woreda_id'),
            'initialOrganizationId' => old('organization_id'),
            ])

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

</x-app-layout>
