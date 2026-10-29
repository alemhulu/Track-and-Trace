@props(['title' => '', 'search' => true, 'entries' => true])
@php
// The header search and "Show N entries" bind to $search and $recordes; leave them out when the
// component has no such property, otherwise typing or changing the count throws a Livewire error.
$component = isset($this) && $this instanceof \Livewire\Component ? $this : null;
$search = $search && (! $component || property_exists($component, 'search')) ? 'search' : '';
$entries = $entries && (! $component || property_exists($component, 'recordes'));
@endphp
<div class="relative card lg:col-span-8 col-span-full">
    <div wire:loading>
        <x-action.cardloader />
    </div>

    <x-form.header search="{{ $search }}">
        {{ __($title) }}
    </x-form.header>

    <div class="overflow-x-auto">
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
    <div>{{ $slot }}</div>
</div>
