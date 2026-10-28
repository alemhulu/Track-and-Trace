@props(['permissions', 'selected' => [], 'name' => 'permission[]', 'readonly' => false])
{{-- Permissions grouped by the module they act on ("create-books" and "view-books" under Books,
     "role-create" under Roles), each group with a select-all toggle. `selected` holds permission ids.
     `readonly` renders the same grouping as badges, e.g. on the role detail page. --}}
@php
$verbs = ['create', 'delete', 'edit', 'view', 'manage', 'update', 'list'];
$selected = array_map('strval', (array) $selected);
$resourceOf = function ($permission) use ($verbs) {
    $parts = explode('-', $permission->name);
    $resource = in_array($parts[0], $verbs) ? array_slice($parts, 1) : array_slice($parts, 0, -1);

    return $resource ? implode(' ', $resource) : $permission->name;
};
// Group "role" and "roles" together, labelled with the longest spelling used.
$groups = collect($permissions)
    ->groupBy(fn ($permission) => \Illuminate\Support\Str::singular($resourceOf($permission)))
    ->mapWithKeys(fn ($items) => [
        \Illuminate\Support\Str::title($items->map($resourceOf)->sortByDesc(fn ($label) => strlen($label))->first()) => $items,
    ])
    ->sortKeys();
@endphp

@if ($readonly)
<div {{ $attributes->merge(['class' => 'grid gap-4 sm:grid-cols-2 xl:grid-cols-3']) }}>
    @forelse ($groups as $group => $items)
    <div class="panel-muted">
        <p class="eyebrow">{{ $group }}</p>
        <div class="flex flex-wrap gap-1.5 mt-2">
            @foreach ($items as $permission)
            <span class="badge-brand">{{ $permission->name }}</span>
            @endforeach
        </div>
    </div>
    @empty
    <x-ui.empty-state class="col-span-full" icon="fi-rr-key" title="No permissions" message="This role has no permissions yet." />
    @endforelse
</div>
@else
<div x-data="{ root: null, count: 0, boxes() { return [...this.root.querySelectorAll('input[data-permission]')] }, recount() { this.count = this.boxes().filter(i => i.checked).length } }"
    x-init="root = $el; recount()" @change="recount()" {{ $attributes->merge(['class' => 'space-y-3']) }}>
    <div class="flex flex-wrap items-center justify-between gap-2">
        <p class="text-sm text-slate-500 dark:text-slate-400">
            <span class="font-bold text-slate-900 dark:text-white" x-text="count"></span> of {{ count($permissions) }} selected
        </p>
        <div class="flex gap-2">
            <button type="button" class="btn-ghost btn-sm"
                @click="boxes().forEach(i => i.checked = true); recount()">Select all</button>
            <button type="button" class="btn-ghost btn-sm"
                @click="boxes().forEach(i => i.checked = false); recount()">Clear</button>
        </div>
    </div>

    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
        @foreach ($groups as $group => $items)
        <fieldset x-data="{ group: null, all() { return [...this.group.querySelectorAll('input[data-permission]')] } }" x-init="group = $el"
            class="p-4 border rounded-xl border-slate-200 dark:border-slate-700">
            <div class="flex items-center justify-between gap-2 pb-2 mb-2 border-b border-slate-100 dark:border-slate-800">
                <legend class="text-sm font-bold text-slate-900 dark:text-white">{{ $group }}</legend>
                <button type="button" class="text-xs whitespace-nowrap shrink-0 link"
                    @click="const on = all().some(i => ! i.checked); all().forEach(i => i.checked = on); recount()">Toggle all</button>
            </div>
            <div class="space-y-1">
                @foreach ($items as $permission)
                <label class="flex items-center gap-2.5 px-2 py-1.5 -mx-2 rounded-lg cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800">
                    <x-checkbox data-permission name="{{ $name }}" value="{{ $permission->id }}"
                        :checked="in_array((string) $permission->id, $selected, true)" />
                    <span class="text-sm text-slate-700 dark:text-slate-300">{{ $permission->name }}</span>
                </label>
                @endforeach
            </div>
        </fieldset>
        @endforeach
    </div>
</div>
@endif
