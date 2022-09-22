<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:text-gray-200">
            {{ __('Organization') }}
        </h2>
    </x-slot>

    <div class="mb-6">
        <x-page.nav col='4'>
            <x-page.nav-link class="bg-yellow-500" title="List" link="organization.list" icon="fi fi-rr-list"
                total="0" />
            <x-page.nav-link class="bg-yellow-600" title="Add Organization" link="organization.add"
                icon="fi fi-rr-plus" />
            <x-page.nav-link class="bg-yellow-700" title="Add Store" link="organization.store-add"
                icon="fi fi-rr-plus" />
            <x-page.nav-link class="bg-yellow-800" title="Type" link="organization.type" icon="fi fi-rr-settings"
                total="0" />
        </x-page.nav>

        <div class="mt-6  tab mb-6">
            @yield('content')
        </div>
    </div>
</x-app-layout>