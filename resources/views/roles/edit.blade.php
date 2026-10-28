<x-app-layout>
    <x-slot name="header">
        <x-ui.breadcrumbs :items="['Roles & Permissions' => 'roles.index', 'Edit role' => null]" class="mb-2" />
        <h2>{{ __('Edit role') }}</h2>
        <p class="page-subtitle">Change the name or permissions of {{ $role->name }}.</p>
    </x-slot>

    <x-slot name="actionButton">
        <a href="{{ route('roles.index') }}" class="btn-secondary">
            <i class="flex fi fi-rr-arrow-left"></i> {{ __('Back to roles') }}
        </a>
    </x-slot>

    <form method="POST" action="{{ route('roles.update', $role->id) }}" class="card">
        @csrf
        @method('PUT')

        <div class="card-body">
            @include('roles.partials.form', ['selected' => old('permission', array_keys($rolePermissions))])
        </div>

        <div class="card-footer">
            <a href="{{ route('roles.index') }}" class="btn-secondary">{{ __('Cancel') }}</a>
            <button type="submit" class="btn-primary">{{ __('Save changes') }}</button>
        </div>
    </form>
</x-app-layout>
