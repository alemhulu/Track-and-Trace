{{-- Grouped in the order books move through the system: print → package → store → route → distribute → trace --}}
<nav class="flex-1 px-3 pb-4" aria-label="Main">
    <p class="nav-section">Overview</p>
    <div class="space-y-0.5">
        <x-side-navigation.link href="/dashboard" icon="fi-rr-dashboard" name="Dashboard"
            :active="request()->routeIs('dashboard')" />
        <x-side-navigation.link href="/dashboard/inventory" icon="fi-rr-chart-histogram" name="Inventory"
            :active="request()->routeIs('dashboard.inventory')" />
        <x-side-navigation.link href="/dashboard/distribution" icon="fi-rr-chart-connected" name="Distribution Insights"
            :active="request()->routeIs('dashboard.distribution')" />
    </div>

    <p class="nav-section">Supply Chain</p>
    <div class="space-y-0.5">
        <x-side-navigation.link href="/book" icon="fi-rr-book" name="Books"
            :active="(request()->routeIs('book') | request()->routeIs('book.*'))" />
        <x-side-navigation.link href="/print-order" icon="fi-rr-print" name="Print Orders"
            :active="(request()->routeIs('print-order') | request()->routeIs('print-order.*'))" />
        <x-side-navigation.link href="/book-packages" icon="fi-rr-box" name="Packages"
            :active="(request()->routeIs('book-packages') | request()->routeIs('packages.*'))" />
        <x-side-navigation.link href="/warehouse" icon="fi-rr-home-location-alt" name="Warehouses"
            :active="request()->routeIs('warehouse')" />
    </div>

    <p class="nav-section">Logistics</p>
    <div class="space-y-0.5">
        <x-side-navigation.link href="/route" icon="fi-rr-chart-tree" name="Routes"
            :active="(request()->routeIs('route') | request()->routeIs('route.*'))" />
        <x-side-navigation.link href="/distribution" icon="fi-rr-truck-side" name="Distribution"
            :active="(request()->routeIs('distribution') | request()->routeIs('distribution.*'))" />
        <x-side-navigation.link href="/trace" icon="fi-rr-search" name="Trace"
            :active="( request()->routeIs('trace') | request()->routeIs('distribution-details.*'))" />
    </div>

    <p class="nav-section">Field Operations</p>
    <div class="space-y-0.5">
        <x-side-navigation.link href="/manual-tracking" icon="fi-rr-cursor-finger" name="Manual Tracking"
            :active="(request()->routeIs('manual-tracking') | request()->routeIs('manual-tracking.*'))" />
    </div>

    <p class="nav-section">Administration</p>
    <div class="space-y-0.5">
        <x-side-navigation.link href="/organization" icon="fi-rr-building" name="Organizations"
            :active="(request()->routeIs('organization') | request()->routeIs('organization.*'))" />
        <x-side-navigation.link href="/location" icon="fi-rr-map-marker-home" name="Locations"
            :active="(request()->routeIs('location') | request()->routeIs('location.*'))" />

        @canany(['view-user', 'user-list'])
        <x-side-navigation.link href="{{ route('users.index') }}" icon="fi-rr-user" name="Users"
            :active="request()->routeIs('users.*')" />
        @endcanany

        @canany(['view-role', 'role-list'])
        <x-side-navigation.link href="{{ route('roles.index') }}" icon="fi-rr-key" name="Roles & Permissions"
            :active="request()->routeIs('roles.*')" />
        @endcanany

        @can('view-logs')
        <x-side-navigation.link href="/log-viewer" icon="fi-rr-info" name="System Logs"
            :active="request()->routeIs('log-viewer')" />
        @endcan
    </div>
</nav>
