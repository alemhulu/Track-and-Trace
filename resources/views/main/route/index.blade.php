<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:text-gray-200">
            {{ __('Router') }}
        </h2>
    </x-slot>

    <div class="mb-6">
        <x-page.nav col='2'>
            <x-page.nav-link class="bg-green-500" title="List" tabLink="List" icon="fi fi-rr-list" total="0" />
            <x-page.nav-link class="bg-green-700" title="Add New Route" tabLink="Route" icon="fi fi-rr-print" />
        </x-page.nav>

        <div id="List" class="mt-6  tab mb-6" style="display:none; ">
            <livewire:route.list-route>
        </div>

        <div id="Route" class="mt-6  tab mb-6" style="display:none; ">
            <livewire:route.add-route>
        </div>
    </div>
</x-app-layout>