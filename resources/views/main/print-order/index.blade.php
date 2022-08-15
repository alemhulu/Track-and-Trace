<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Print Order') }}
        </h2>
    </x-slot>

    <div class="mb-6">
        <x-page.nav col='2'>
            <x-page.nav-link class="bg-blue-500" title="List" tabLink="List" icon="fi fi-rr-list" total="0" />
            <x-page.nav-link class="bg-blue-700" title="Order New Print" tabLink="Order" icon="fi fi-rr-print" />
        </x-page.nav>

        <div id="List" class="mt-6  tab mb-6" style="display:none; ">
            <livewire:print.list-print-order>
        </div>

        <div id="Order" class="mt-6  tab mb-6" style="display:none; ">
            <livewire:print.add-print-order>
        </div>
    </div>
</x-app-layout>