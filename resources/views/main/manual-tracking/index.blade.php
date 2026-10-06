<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
            {{ __('Manual Tracking') }}
        </h2>
    </x-slot>

    <div class="mb-6">
        <x-page.nav col='4'>
            <x-page.nav-link class="bg-yellow-600" title="Books Management" link="manual-tracking.books.list"
                icon="fi fi-rr-book" />
            <x-page.nav-link class="bg-blue-600" title="Packages Management" link="manual-tracking.packages.list"
                icon="fi fi-rr-box" />
            <x-page.nav-link class="bg-green-600" title="Distribution Entry" link="manual-tracking.distribution.list"
                icon="fi fi-rr-truck-side" />
            <x-page.nav-link class="bg-indigo-600" title="Reports" link="manual-tracking.reports.index"
                icon="fi fi-rr-chart-histogram" />
        </x-page.nav>

        <div class="mt-6 mb-6 tab">
            @yield('content')
        </div>
    </div>
</x-app-layout>
