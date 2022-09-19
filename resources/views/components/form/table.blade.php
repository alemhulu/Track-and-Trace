@props(['title' => '', 'search' => true ])
@php
if($search==true ) {
$search = "search";
}
else {
$search = "";
}
@endphp
<div class="relative py-6  lg:py-3 bg-white sm:rounded-lg lg:col-span-8 col-span-full overflow-auto">

    <x-form.header search="{{ $search }}">
        {{ __($title) }}
    </x-form.header>

    <x-data-table.table class="myTable2 mb-14">
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