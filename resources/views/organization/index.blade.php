<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Organization') }}
        </h2>
    </x-slot>

    <div class="mb-6">
        <x-page.nav col='3'>
            <x-page.nav-link class="bg-yellow-500" title="List" tabLink="List" icon="fi fi-rr-list" total="0" />
            <x-page.nav-link class="bg-yellow-700" title="Add Organization" tabLink="Add" icon="fi fi-rr-plus" />
            <x-page.nav-link class="bg-yellow-900" title="Type" tabLink="Type" icon="fi fi-rr-settings" total="0" />
        </x-page.nav>

        <div id="List" class="mt-6  tab mb-6" style="display:none; ">
            <livewire:oganization.list-organization>
        </div>

        <div id="Add" class="mt-6  tab mb-6" style="display:none; ">
            <livewire:oganization.add-organization>
        </div>

        <div id="Type" class="mt-6  tab mb-6" style="display:none; ">
            <livewire:oganization.organization-type>
        </div>
    </div>
</x-app-layout>