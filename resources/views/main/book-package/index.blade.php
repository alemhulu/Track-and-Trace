<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:text-gray-200">
            {{ __('Packages') }}
        </h2>
    </x-slot>

    <div class="mb-6">
        <x-page.nav col='4'>
            <x-page.nav-link class="bg-blue-500" title="Info" link="book-packages" icon="fi fi-rr-info" total="0" />
            <x-page.nav-link class="bg-red-600" title="Request" link="packages.request" icon="fi fi-rr-inbox"
                total="0" />
            <x-page.nav-link class="bg-yellow-500" title="Send" link="packages.send" icon="fi fi-rr-paper-plane"
                total="0" />
            <x-page.nav-link class="bg-green-700" title="Available" link="packages.available" icon="fi fi-rr-box"
                total="0" />
        </x-page.nav>

        <div class="mt-6 tab mb-6">
            @yield('content')
        </div>
    </div>
</x-app-layout>