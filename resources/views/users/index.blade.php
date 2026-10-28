<x-app-layout>
    <x-slot name="header">
        <h2>{{ __('User Management') }}</h2>
        <p class="page-subtitle">People who can sign in, the area they cover and the roles they hold.</p>
    </x-slot>

    <x-slot name="actionButton">
        <a href="{{ route('users.create') }}" class="btn-primary">
            <i class="flex fi fi-rr-user-add"></i> {{ __('New user') }}
        </a>
    </x-slot>

    <x-admin.nav class="mb-6" />

    @php
    $activeFilters = collect($filters)->filter(fn ($value) => $value !== '' && $value !== null)->count();
    $accounts = $data->total() . ' ' . \Illuminate\Support\Str::plural('account', $data->total());
    @endphp

    <x-ui.card title="Users" subtitle="{{ $activeFilters ? $accounts . ' match your filters' : $accounts }}" icon="fi-rr-user" flush>
        <form method="GET" action="{{ route('users.index') }}" role="search"
            class="flex flex-wrap items-end gap-3 px-5 py-4 border-b border-slate-100 dark:border-slate-800">
            <input type="hidden" name="per_page" value="{{ $perPage }}">

            <div class="flex-1 min-w-[14rem]">
                <label for="user-search" class="sr-only">{{ __('Search users') }}</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                        <i class="flex fi fi-rr-search"></i>
                    </div>
                    <input id="user-search" type="search" name="q" value="{{ $filters['q'] }}" class="form-control pl-9"
                        placeholder="{{ __('Search name, email or organization…') }}">
                </div>
            </div>

            <div class="w-full sm:w-40">
                <label for="filter-access" class="sr-only">{{ __('Access level') }}</label>
                <select id="filter-access" name="access_level" class="form-control" onchange="this.form.submit()">
                    <option value="">{{ __('All access levels') }}</option>
                    @foreach ($filterOptions['accessLevels'] as $level)
                    <option value="{{ $level }}" @selected($filters['access_level'] === $level)>{{ ucfirst($level) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="w-full sm:w-44">
                <label for="filter-role" class="sr-only">{{ __('Role') }}</label>
                <select id="filter-role" name="role" class="form-control" onchange="this.form.submit()">
                    <option value="">{{ __('All roles') }}</option>
                    @foreach ($filterOptions['roles'] as $role)
                    <option value="{{ $role }}" @selected($filters['role'] === $role)>{{ $role }}</option>
                    @endforeach
                </select>
            </div>

            @if (count($filterOptions['regions']) > 1)
            <div class="w-full sm:w-44">
                <label for="filter-region" class="sr-only">{{ __('Region') }}</label>
                <select id="filter-region" name="region_id" class="form-control" onchange="this.form.submit()">
                    <option value="">{{ __('All regions') }}</option>
                    @foreach ($filterOptions['regions'] as $id => $name)
                    <option value="{{ $id }}" @selected($filters['region_id'] === $id)>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            <div class="flex items-center gap-2">
                <button type="submit" class="btn-primary">{{ __('Search') }}</button>
                @if ($activeFilters)
                <a href="{{ route('users.index', ['per_page' => $perPage]) }}" class="btn-ghost">
                    <i class="flex fi fi-rr-cross-small"></i> {{ __('Clear') }}
                    <span class="badge-brand">{{ $activeFilters }}</span>
                </a>
                @endif
            </div>
        </form>

        <x-data-table.per-page :value="$perPage" />

        <div class="overflow-x-auto">
            <table class="table-ui">
                <thead>
                    <tr>
                        <th>{{ __('User') }}</th>
                        <th>{{ __('Access') }}</th>
                        <th>{{ __('Location') }}</th>
                        <th>{{ __('Roles') }}</th>
                        <th><span class="sr-only">{{ __('Actions') }}</span></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($data as $user)
                    @php
                    $place = collect([$user->organization, $user->woreda, $user->zone, $user->region])->filter()->pluck('name');
                    @endphp
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                <x-admin.avatar :name="$user->name" />
                                <div class="min-w-0">
                                    <p class="font-bold truncate text-slate-900 dark:text-white">{{ $user->name }}</p>
                                    <p class="text-xs truncate text-slate-500 dark:text-slate-400">{{ $user->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td>
                            <x-ui.badge tone="brand">{{ ucfirst($user->effectiveAccessLevel()) }}</x-ui.badge>
                        </td>
                        <td class="min-w-[12rem]">
                            @if ($place->isNotEmpty())
                            <p class="font-semibold text-slate-800 dark:text-slate-100">{{ $place->first() }}</p>
                            @if ($place->count() > 1)
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $place->slice(1)->implode(' · ') }}</p>
                            @endif
                            @else
                            <span class="text-slate-400">{{ __('All locations') }}</span>
                            @endif
                        </td>
                        <td>
                            <div class="flex flex-wrap gap-1 max-w-xs">
                                @forelse ($user->getRoleNames() as $role)
                                <x-ui.badge tone="success">{{ $role }}</x-ui.badge>
                                @empty
                                <span class="text-xs text-slate-400">{{ __('No roles') }}</span>
                                @endforelse
                            </div>
                        </td>
                        <td>
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('users.show', $user->id) }}" class="icon-btn-view" title="{{ __('View') }}">
                                    <i class="flex fi fi-rr-eye"></i>
                                </a>
                                <a href="{{ route('users.edit', $user->id) }}" class="icon-btn-edit" title="{{ __('Edit') }}">
                                    <i class="flex fi fi-rr-edit"></i>
                                </a>
                                <form method="POST" action="{{ route('users.destroy', $user->id) }}"
                                    onsubmit="return confirm(@js(__('Delete :name? This cannot be undone.', ['name' => $user->name])))">
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
                        <td colspan="5">
                            @if ($activeFilters)
                            <x-ui.empty-state icon="fi-rr-search" title="{{ __('No users match these filters') }}" message="{{ __('Try a different search term or clear the filters.') }}">
                                <a href="{{ route('users.index', ['per_page' => $perPage]) }}" class="btn-secondary btn-sm">{{ __('Clear filters') }}</a>
                            </x-ui.empty-state>
                            @else
                            <x-ui.empty-state icon="fi-rr-user" title="{{ __('No users yet') }}" message="{{ __('Create the first account to give someone access.') }}">
                                <a href="{{ route('users.create') }}" class="btn-primary btn-sm">{{ __('New user') }}</a>
                            </x-ui.empty-state>
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $data->links() }}
    </x-ui.card>
</x-app-layout>
