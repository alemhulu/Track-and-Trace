<x-app-layout>
    <x-slot name="header">
        <x-ui.breadcrumbs :items="['Roles & Permissions' => 'roles.index', $role->name => null]" class="mb-2" />
        <h2>{{ $role->name }}</h2>
        <p class="page-subtitle">{{ $rolePermissions->count() }} {{ \Illuminate\Support\Str::plural('permission', $rolePermissions->count()) }} granted</p>
    </x-slot>

    <x-slot name="actionButton">
        <a href="{{ route('roles.index') }}" class="btn-secondary">
            <i class="flex fi fi-rr-arrow-left"></i> {{ __('Back to roles') }}
        </a>
        <a href="{{ route('roles.edit', $role->id) }}" class="btn-primary">
            <i class="flex fi fi-rr-edit"></i> {{ __('Edit role') }}
        </a>
    </x-slot>

    <x-ui.card title="Permissions" subtitle="Grouped by the module they apply to." icon="fi-rr-key">
        <x-admin.permission-picker :permissions="$rolePermissions" readonly />
    </x-ui.card>
</x-app-layout>
