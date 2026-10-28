@props(['permissions', 'selected' => [], 'name' => 'permission[]', 'readonly' => false])
{{-- Permissions grouped like the sidebar (config/permissions.php): section → module → permissions,
     and for modules made of several screens, one row per sub-module (Countries: View Create Edit
     Delete). Select-all toggles per section, module and sub-module. `selected` holds permission ids.
     Permissions outside the catalog are listed under "Other". `readonly` renders the same
     grouping as badges, e.g. on the role detail page. --}}
@php
$catalog = \App\Support\PermissionCatalog::class;
$selected = array_map('strval', (array) $selected);
$byName = collect($permissions)->keyBy('name');
$pick = fn (array $names) => collect(array_keys($names))->filter(fn ($permission) => $byName->has($permission))
    ->map(fn ($permission) => $byName[$permission])->values();
// Inside a sub-module row "View regions" reads as just "View"
$shortLabel = fn ($permission) => in_array($action = \Illuminate\Support\Str::afterLast($permission->name, '.'), ['view', 'create', 'edit', 'delete'], true)
    ? ucfirst($action) : $catalog::labelOf($permission->name);

$sections = [];
foreach ($catalog::sections() as $section => $modules) {
    foreach ($modules as $key => $module) {
        $groups = collect($module['submodules'] ?? [])
            ->map(fn ($submodule) => ['label' => $submodule['label'], 'items' => $pick($submodule['permissions'])])
            ->filter(fn ($group) => $group['items']->isNotEmpty());
        $items = $pick($module['permissions']);
        if ($items->isNotEmpty() || $groups->isNotEmpty()) {
            $sections[$section][$key] = ['label' => $module['label'], 'icon' => $module['icon'], 'items' => $items, 'groups' => $groups];
        }
    }
}

$other = $byName->except($catalog::names())->values();
if ($other->isNotEmpty()) {
    $sections['Other']['other'] = ['label' => 'Other', 'icon' => 'fi-rr-key', 'items' => $other, 'groups' => collect()];
}
@endphp

@if ($readonly)
<div {{ $attributes->merge(['class' => 'space-y-6']) }}>
    @forelse ($sections as $section => $modules)
    <section>
        <p class="mb-2 eyebrow">{{ $section }}</p>
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($modules as $module)
            <div @class(['panel-muted', 'sm:col-span-2 xl:col-span-3' => $module['groups']->isNotEmpty()])>
                <p class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white">
                    <i class="flex fi {{ $module['icon'] }} text-brand-500 dark:text-brand-300"></i> {{ $module['label'] }}
                </p>
                @if ($module['items']->isNotEmpty())
                <div class="flex flex-wrap gap-1.5 mt-2">
                    @foreach ($module['items'] as $permission)
                    <span class="badge-brand" title="{{ $permission->name }}">{{ $catalog::labelOf($permission->name) }}</span>
                    @endforeach
                </div>
                @endif
                @if ($module['groups']->isNotEmpty())
                <div class="grid gap-x-6 gap-y-2 mt-3 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach ($module['groups'] as $group)
                    <div>
                        <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">{{ $group['label'] }}</p>
                        <div class="flex flex-wrap gap-1.5 mt-1">
                            @foreach ($group['items'] as $permission)
                            <span class="badge-brand" title="{{ $permission->name }}">{{ $shortLabel($permission) }}</span>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
            @endforeach
        </div>
    </section>
    @empty
    <x-ui.empty-state icon="fi-rr-key" title="No permissions" message="This role has no permissions yet." />
    @endforelse
</div>
@else
<div x-data="{ root: null, count: 0, boxes(el = this.root) { return [...el.querySelectorAll('input[data-permission]')] }, recount() { this.count = this.boxes().filter(i => i.checked).length }, toggle(el) { const on = this.boxes(el).some(i => ! i.checked); this.boxes(el).forEach(i => i.checked = on); this.recount() } }"
    x-init="root = $el; recount()" @change="recount()" {{ $attributes->merge(['class' => 'space-y-6']) }}>
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

    @foreach ($sections as $section => $modules)
    <section class="space-y-3">
        <div class="flex items-center justify-between gap-2">
            <p class="eyebrow">{{ $section }}</p>
            <button type="button" class="text-xs whitespace-nowrap link"
                @click="toggle($el.closest('section'))">Toggle section</button>
        </div>

        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($modules as $module)
            <fieldset @class(['p-4 border rounded-xl border-slate-200 dark:border-slate-700', 'sm:col-span-2 xl:col-span-3' => $module['groups']->isNotEmpty()])>
                <div class="flex items-center justify-between gap-2 pb-2 mb-2 border-b border-slate-100 dark:border-slate-800">
                    <legend class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white">
                        <i class="flex fi {{ $module['icon'] }} text-brand-500 dark:text-brand-300"></i> {{ $module['label'] }}
                    </legend>
                    <button type="button" class="text-xs whitespace-nowrap shrink-0 link"
                        @click="toggle($el.closest('fieldset'))">Toggle all</button>
                </div>

                @if ($module['items']->isNotEmpty())
                <div @class(['space-y-1', 'grid sm:grid-cols-2 xl:grid-cols-3 gap-x-6 space-y-0' => $module['groups']->isNotEmpty()])>
                    @foreach ($module['items'] as $permission)
                    <label class="flex items-start gap-2.5 px-2 py-1.5 -mx-2 rounded-lg cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800">
                        <x-checkbox data-permission name="{{ $name }}" value="{{ $permission->id }}" class="mt-0.5"
                            :checked="in_array((string) $permission->id, $selected, true)" />
                        <span class="min-w-0">
                            <span class="block text-sm text-slate-700 dark:text-slate-300">{{ $catalog::labelOf($permission->name) }}</span>
                            <span class="block text-[11px] font-mono text-slate-400 dark:text-slate-500 truncate">{{ $permission->name }}</span>
                        </span>
                    </label>
                    @endforeach
                </div>
                @endif

                @if ($module['groups']->isNotEmpty())
                <div class="grid gap-3 pt-3 mt-2 border-t border-slate-100 dark:border-slate-800 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach ($module['groups'] as $group)
                    <div data-group class="p-3 rounded-lg bg-slate-50 dark:bg-slate-800/50">
                        <div class="flex items-center justify-between gap-2">
                            <p class="text-xs font-bold tracking-wide uppercase text-slate-600 dark:text-slate-300">{{ $group['label'] }}</p>
                            <button type="button" class="text-xs whitespace-nowrap link"
                                @click="toggle($el.closest('[data-group]'))">Toggle</button>
                        </div>
                        <div class="flex flex-wrap gap-x-4 gap-y-1 mt-2">
                            @foreach ($group['items'] as $permission)
                            <label class="inline-flex items-center gap-2 py-0.5 text-sm cursor-pointer text-slate-700 dark:text-slate-300" title="{{ $permission->name }}">
                                <x-checkbox data-permission name="{{ $name }}" value="{{ $permission->id }}"
                                    :checked="in_array((string) $permission->id, $selected, true)" />
                                {{ $shortLabel($permission) }}
                            </label>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </fieldset>
            @endforeach
        </div>
    </section>
    @endforeach
</div>
@endif
