@props(['title' => '', 'search' => true ])
@php
if($search==true ) {
$search = "search";
}
else {
$search = "";
}
@endphp
<div
    class="relative py-6  lg:py-3 bg-white dark:bg-gray-800 rounded-md sm:rounded-lg lg:col-span-8 col-span-full overflow-auto">
    <div wire:loading>
        <x-action.cardloader />
    </div>

    <x-form.header search="{{ $search }}">
        {{ __($title) }}
    </x-form.header>

    <x-data-table.table class="myTable2 mb-14">
        <thead class="bg-lime-50 dark:bg-gray-700">
            <x-data-table.tr>
                {{ $tableHeaders ?? $slot }}
            </x-data-table.tr>
        </thead>

        <tbody class="">
            {{ $tableRows }}
        </tbody>
    </x-data-table.table>
    {{ $slot }}
</div>