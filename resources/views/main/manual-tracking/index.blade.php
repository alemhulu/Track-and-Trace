<x-app-layout>
    <x-slot name="header">
        <h2>{{ __('Manual Tracking') }}</h2>
        <p class="page-subtitle">Follow every title from the printer to the classroom, one step at a time.</p>
    </x-slot>

    <div class="mb-6 space-y-3">
        @php
            $schoolUser = auth()->user()?->isSchoolUser();
        @endphp
        <x-ui.workflow label="Distribution workflow" :steps="array_values(array_filter([
            ['title' => 'Titles', 'hint' => 'Register books', 'link' => 'manual-tracking.books.list', 'match' => 'manual-tracking.books.*', 'icon' => 'fi-rr-book'],
            ['title' => 'Stock', 'hint' => 'Receive from printers', 'link' => 'manual-tracking.stock.index', 'icon' => 'fi-rr-box'],
            ['title' => 'Shipments', 'hint' => $schoolUser ? 'Receive deliveries' : 'Dispatch & receive', 'link' => 'manual-tracking.distribution.list', 'match' => ['manual-tracking.distribution.*', 'manual-tracking.shipments.*'], 'icon' => 'fi-rr-truck-side'],
            $schoolUser ? null : ['title' => 'Sort & Scan', 'hint' => 'Route at hubs', 'link' => 'manual-tracking.scan.sort', 'icon' => 'fi-rr-camera'],
            ['title' => 'Schools', 'hint' => 'Issue to students', 'link' => 'manual-tracking.schools.index', 'match' => 'manual-tracking.schools.*', 'icon' => 'fi-rr-school'],
            ['title' => 'Visits', 'hint' => 'Verify on site', 'link' => 'manual-tracking.visits.index', 'match' => 'manual-tracking.visits.*', 'icon' => 'fi-rr-checkbox'],
        ]))" />

        <x-page.nav>
            <li class="flex items-center px-3 eyebrow shrink-0">Monitor</li>
            <x-page.nav-link title="Discrepancies" link="manual-tracking.discrepancies.index" icon="fi fi-rr-exclamation" />
            <x-page.nav-link title="Misroutes" link="manual-tracking.scan.alerts" icon="fi fi-rr-shuffle" />
            <x-page.nav-link title="Reports" link="manual-tracking.reports.index" icon="fi fi-rr-chart-histogram" />
            <x-page.nav-link title="Field App" link="field.app" icon="fi fi-rr-smartphone" />
        </x-page.nav>

        <div class="pt-3 tab">
            @yield('content')
        </div>
    </div>
</x-app-layout>
