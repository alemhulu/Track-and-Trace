<x-app-layout>
    <x-slot name="header">
        <h2>{{ __('Roles & Permissions') }}</h2>
        <p class="page-subtitle">Bundle permissions into roles, then assign roles to users.</p>
    </x-slot>

    <x-slot name="actionButton">
        <a href="{{ route('roles.create') }}" class="btn-primary">
            <i class="flex fi fi-rr-plus"></i> {{ __('New role') }}
        </a>
    </x-slot>

    <x-admin.nav class="mb-6" />

    <x-ui.card title="Roles" subtitle="{{ $roles->total() }} {{ \Illuminate\Support\Str::plural('role', $roles->total()) }}" icon="fi-rr-key" flush>
        <x-data-table.per-page :value="$perPage" />

        <div class="overflow-x-auto">
            <table class="table-ui">
                <thead>
                    <tr>
                        <th>{{ __('Role') }}</th>
                        <th>{{ __('Permissions') }}</th>
                        <th><span class="sr-only">{{ __('Actions') }}</span></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($roles as $role)
                    @php
                    $permissionNames = $role->permissions->pluck('name');
                    $modules = \App\Support\PermissionCatalog::moduleLabelsFor($permissionNames);
                    @endphp
                    <tr>
                        <td class="w-1/4">
                            <div class="flex items-center gap-3">
                                <span class="flex items-center justify-center rounded-lg w-9 h-9 shrink-0 bg-gold-50 text-gold-600 ring-1 ring-inset ring-gold-100 dark:bg-gold-900/30 dark:text-gold-300 dark:ring-gold-900">
                                    <i class="flex fi fi-rr-key"></i>
                                </span>
                                <p class="font-bold text-slate-900 dark:text-white">{{ $role->name }}</p>
                            </div>
                        </td>
                        <td>
                            <div class="flex flex-wrap items-center gap-1">
                                <x-ui.badge tone="brand">{{ $permissionNames->count() }} {{ \Illuminate\Support\Str::plural('permission', $permissionNames->count()) }}</x-ui.badge>
                                @foreach (array_slice($modules, 0, 4) as $module)
                                <x-ui.badge tone="neutral">{{ $module }}</x-ui.badge>
                                @endforeach
                                @if (count($modules) > 4)
                                <span class="text-xs font-semibold text-slate-500">+{{ count($modules) - 4 }} more {{ \Illuminate\Support\Str::plural('module', count($modules) - 4) }}</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('roles.show', $role->id) }}" class="icon-btn-view" title="{{ __('View') }}">
                                    <i class="flex fi fi-rr-eye"></i>
                                </a>
                                <a href="{{ route('roles.edit', $role->id) }}" class="icon-btn-edit" title="{{ __('Edit') }}">
                                    <i class="flex fi fi-rr-edit"></i>
                                </a>
                                <form method="POST" action="{{ route('roles.destroy', $role->id) }}"
                                    onsubmit="return confirm(@js(__('Delete the :name role? Users holding it will lose its permissions.', ['name' => $role->name])))">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="icon-btn-delete" title="{{ __('Delete') }}">
                                        <i class="flex fi fi-rr-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3">
                            <x-ui.empty-state icon="fi-rr-key" title="{{ __('No roles yet') }}" message="{{ __('Create a role to start granting permissions.') }}">
                                <a href="{{ route('roles.create') }}" class="btn-primary btn-sm">{{ __('New role') }}</a>
                            </x-ui.empty-state>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $roles->links() }}
    </x-ui.card>
</x-app-layout>
