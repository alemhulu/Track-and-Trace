@props(['title' => '', 'search' => true, 'entries' => true, 'collapsible' => false, 'subtitle' => null])
@php
// The header search and "Show N entries" bind to $search and $recordes; leave them out when the
// component has no such property, otherwise typing or changing the count throws a Livewire error.
$component = isset($this) && $this instanceof \Livewire\Component ? $this : null;
$search = $search && (! $component || property_exists($component, 'search')) ? 'search' : '';
$entries = $entries && (! $component || property_exists($component, 'recordes'));
@endphp
{{-- `collapsible` adds a toggle to the header; pass a string to name the remembered open state (defaults to the title) --}}
<div class="relative card lg:col-span-8 col-span-full"
    @if ($collapsible)
    x-data="{
        key: @js('table-open:' . (is_string($collapsible) ? $collapsible : $title)),
        open: true,
        init() {
            try { this.open = localStorage.getItem(this.key) !== '0' } catch (e) {}
            this.$watch('open', value => { try { localStorage.setItem(this.key, value ? '1' : '0') } catch (e) {} })
        },
    }"
    @endif>
    <div wire:loading>
        <x-action.cardloader />
    </div>

    <x-form.header search="{{ $search }}" :collapsible="$collapsible" :subtitle="$subtitle">
        {{ __($title) }}
    </x-form.header>

    <div class="overflow-x-auto" @if ($collapsible) x-show="open" x-cloak @endif>
        <x-data-table.table class="myTable2" :entries="$entries">
            <thead class="bg-slate-50 border-y border-slate-100 dark:bg-slate-800/60 dark:border-slate-800">
                <tr>
                    {{ $tableHeaders ?? $slot }}
                </tr>
            </thead>

            <tbody>
                {{ $tableRows }}
            </tbody>
        </x-data-table.table>
    </div>
    <div @if ($collapsible) x-show="open" x-cloak @endif>{{ $slot }}</div>
</div>
