<x-app-layout>
    <x-slot name="header">
        <x-ui.breadcrumbs :items="['Users' => 'users.index', 'New user' => null]" class="mb-2" />
        <h2>{{ __('New user') }}</h2>
        <p class="page-subtitle">Create an account and decide what it can access.</p>
    </x-slot>

    <x-slot name="actionButton">
        <a href="{{ route('users.index') }}" class="btn-secondary">
            <i class="flex fi fi-rr-arrow-left"></i> {{ __('Back to users') }}
        </a>
    </x-slot>

    <form method="POST" action="{{ route('users.store') }}" class="card">
        @csrf

        <div class="card-body">
            @include('users.partials.form', [
                'selectedRoles' => old('roles', []),
                'cascade' => [
                'mode' => 'create',
                'initialAccessLevel' => old('access_level', 'organization'),
                'initialCountryId' => old('country_id'),
                'initialRegionId' => old('region_id'),
                'initialZoneId' => old('zone_id'),
                'initialWoredaId' => old('woreda_id'),
                'initialOrganizationId' => old('organization_id'),
                ],
            ])
        </div>

        <div class="card-footer">
            <a href="{{ route('users.index') }}" class="btn-secondary">{{ __('Cancel') }}</a>
            <button type="submit" class="btn-primary">{{ __('Create user') }}</button>
        </div>
    </form>
</x-app-layout>
