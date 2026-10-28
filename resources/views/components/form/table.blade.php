@props(['title' => '', 'search' => true ])
@php
if($search==true ) {
$search = "search";
}
else {
$search = "";
}
@endphp
<div class="relative card lg:col-span-8 col-span-full">
    <div wire:loading>
        <x-action.cardloader />
    </div>

    <x-form.header search="{{ $search }}">
        {{ __($title) }}
    </x-form.header>

    <div class="overflow-x-auto">
        <x-data-table.table class="myTable2">
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
