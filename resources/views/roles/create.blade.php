<x-app-layout>
    <x-slot name="header">
        <x-ui.breadcrumbs :items="['Roles & Permissions' => 'roles.index', 'New role' => null]" class="mb-2" />
        <h2>{{ __('New role') }}</h2>
        <p class="page-subtitle">Name the role and choose what it is allowed to do.</p>
    </x-slot>

    <x-slot name="actionButton">
        <a href="{{ route('roles.index') }}" class="btn-secondary">
            <i class="flex fi fi-rr-arrow-left"></i> {{ __('Back to roles') }}
        </a>
    </x-slot>

    <form method="POST" action="{{ route('roles.store') }}" class="card">
        @csrf

        <div class="card-body">
            @include('roles.partials.form', ['selected' => old('permission', [])])
        </div>

        <div class="card-footer">
            <a href="{{ route('roles.index') }}" class="btn-secondary">{{ __('Cancel') }}</a>
            <button type="submit" class="btn-primary">{{ __('Create role') }}</button>
        </div>
    </form>
</x-app-layout>
