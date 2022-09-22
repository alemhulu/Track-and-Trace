<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:text-gray-200">
            {{ __('Location') }}
        </h2>
    </x-slot>

    <div class="mb-6">
        <x-page.nav col='5'>
            <x-page.nav-link class="bg-yellow-600" title="Country" link="location.country" icon="fi fi-rr-plus"
                total="0" />
            <x-page.nav-link class="bg-green-600" title="Region / City" link="location.region" icon="fi fi-rr-plus"
                total="0" />
            <x-page.nav-link class="bg-blue-800" title="Zone / SubCity" link="location.zone" icon="fi fi-rr-plus"
                total="0" />
            <x-page.nav-link class="bg-pink-800" title="Woreda" link="location.woreda" icon="fi fi-rr-plus" total="0" />
            <x-page.nav-link class="bg-blue-500" title="kebele" link="location.kebele" icon="fi fi-rr-plus" total="0" />
        </x-page.nav>

        <div class="mt-6  tab mb-6">
            @yield('content')
        </div>
    </div>
</x-app-layout>