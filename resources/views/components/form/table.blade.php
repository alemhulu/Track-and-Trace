@props(['title' => '', 'search' => true ])
@php
if($search==true ) {
$search = "search";
}
else {
$search = "";
}
@endphp
<div class="relative py-6 px-5 sm:px-6 lg:py-0 lg:px-0 lg:col-span-8 bg-white sm:rounded-lg">

    <x-form.header search="{{ $search }}">
        {{ __($title) }}
    </x-form.header>

    <x-data-table.table class="myTable2 mb-14 overflow-x-auto">
        <thead class="bg-lime-50">
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