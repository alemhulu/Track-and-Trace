<x-app-layout>
    <x-slot name="header">
        <x-ui.breadcrumbs :items="['Users' => 'users.index', $user->name => null]" class="mb-2" />
        <h2>{{ $user->name }}</h2>
        <p class="page-subtitle">{{ $user->email }}</p>
    </x-slot>

    <x-slot name="actionButton">
        <a href="{{ route('users.index') }}" class="btn-secondary">
            <i class="flex fi fi-rr-arrow-left"></i> {{ __('Back to users') }}
        </a>
        <a href="{{ route('users.edit', $user->id) }}" class="btn-primary">
            <i class="flex fi fi-rr-edit"></i> {{ __('Edit user') }}
        </a>
    </x-slot>

    @php
    $places = [
        'Country' => $user->country?->name,
        'Region' => $user->region?->name,
        'Zone' => $user->zone?->name,
        'Woreda' => $user->woreda?->name,
        'Organization' => $user->organization?->name,
    ];
    @endphp

    <div class="grid gap-6 lg:grid-cols-3">
        <x-ui.card class="lg:col-span-1">
            <div class="flex flex-col items-center text-center">
                <x-admin.avatar :name="$user->name" class="!w-16 !h-16 !text-xl" />
                <p class="mt-3 text-lg font-extrabold text-slate-900 dark:text-white">{{ $user->name }}</p>
                <p class="text-sm text-slate-500 dark:text-slate-400">{{ $user->email }}</p>
                <x-ui.badge tone="brand" class="mt-3">{{ ucfirst($user->effectiveAccessLevel()) }} access</x-ui.badge>
            </div>
        </x-ui.card>

        <div class="space-y-6 lg:col-span-2">
            <x-ui.card title="Access scope" subtitle="Where this user can see and act." icon="fi-rr-map-marker">
                <dl class="grid gap-4 sm:grid-cols-2">
                    @foreach ($places as $label => $value)
                    <div>
                        <dt class="eyebrow">{{ $label }}</dt>
                        <dd class="mt-1 text-sm font-semibold {{ $value ? 'text-slate-900 dark:text-white' : 'text-slate-400' }}">{{ $value ?? 'Any' }}</dd>
                    </div>
                    @endforeach
                </dl>
            </x-ui.card>

            <x-ui.card title="Roles" subtitle="Roles decide which screens and actions are available." icon="fi-rr-key">
                <div class="flex flex-wrap gap-2">
                    @forelse ($user->getRoleNames() as $role)
                    <x-ui.badge tone="success">{{ $role }}</x-ui.badge>
                    @empty
                    <p class="text-sm text-slate-500">{{ __('No roles assigned.') }}</p>
                    @endforelse
                </div>
            </x-ui.card>
        </div>
    </div>
</x-app-layout>
