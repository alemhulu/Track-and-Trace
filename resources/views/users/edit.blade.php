<x-app-layout>
    <x-slot name="header">
        <x-ui.breadcrumbs :items="['Users' => 'users.index', 'Edit user' => null]" class="mb-2" />
        <h2>{{ __('Edit user') }}</h2>
        <p class="page-subtitle">Update details, access scope and roles for {{ $user->name }}.</p>
    </x-slot>

    <x-slot name="actionButton">
        <a href="{{ route('users.index') }}" class="btn-secondary">
            <i class="flex fi fi-rr-arrow-left"></i> {{ __('Back to users') }}
        </a>
    </x-slot>

    <form method="POST" action="{{ route('users.update', $user->id) }}" class="card">
        @csrf
        @method('PUT')

        <div class="card-body">
            @include('users.partials.form', [
                'selectedRoles' => old('roles', array_keys($userRole)),
                'cascade' => [
                'mode' => 'edit',
                'initialAccessLevel' => old('access_level', $user->effectiveAccessLevel()),
                'initialCountryId' => old('country_id', $user->country_id),
                'initialRegionId' => old('region_id', $user->region_id),
                'initialZoneId' => old('zone_id', $user->zone_id),
                'initialWoredaId' => old('woreda_id', $user->woreda_id),
                'initialOrganizationId' => old('organization_id', $user->organization_id),
                ],
            ])
        </div>

        <div class="card-footer">
            <a href="{{ route('users.index') }}" class="btn-secondary">{{ __('Cancel') }}</a>
            <button type="submit" class="btn-primary">{{ __('Save changes') }}</button>
        </div>
    </form>
</x-app-layout>
