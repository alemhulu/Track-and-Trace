<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
            {{ __('Manual Tracking') }}
        </h2>
    </x-slot>

    <div class="mb-6">
        <x-page.nav col='10'>
            <x-page.nav-link class="bg-yellow-600" title="Titles" link="manual-tracking.books.list"
                icon="fi fi-rr-book" />
            <x-page.nav-link class="bg-blue-600" title="Stock" link="manual-tracking.stock.index"
                icon="fi fi-rr-box" />
            <x-page.nav-link class="bg-green-600" title="Shipments" link="manual-tracking.distribution.list"
                icon="fi fi-rr-truck-side" />
            <x-page.nav-link class="bg-purple-600" title="Sort & Scan" link="manual-tracking.scan.sort"
                icon="fi fi-rr-camera" />
            <x-page.nav-link class="bg-red-600" title="Discrepancies" link="manual-tracking.discrepancies.index"
                icon="fi fi-rr-exclamation" />
            <x-page.nav-link class="bg-orange-600" title="Misroutes" link="manual-tracking.scan.alerts"
                icon="fi fi-rr-shuffle" />
            <x-page.nav-link class="bg-teal-600" title="Schools" link="manual-tracking.schools.index"
                icon="fi fi-rr-school" />
            <x-page.nav-link class="bg-green-700" title="Visits" link="manual-tracking.visits.index"
                icon="fi fi-rr-checkbox" />
            <x-page.nav-link class="bg-gray-700" title="Field App" link="field.app"
                icon="fi fi-rr-smartphone" />
            <x-page.nav-link class="bg-indigo-600" title="Reports" link="manual-tracking.reports.index"
                icon="fi fi-rr-chart-histogram" />
        </x-page.nav>

        <div class="mt-6 mb-6 tab">
            @yield('content')
        </div>
    </div>
</x-app-layout>
