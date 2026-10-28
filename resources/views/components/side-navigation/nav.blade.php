{{-- Grouped in the order books move through the system: print → package → store → route → distribute → trace.
     Sections and links follow config/permissions.php: a link shows with its module's view permission,
     a section heading once any of its links does. --}}
<nav class="flex-1 px-3 pb-4" aria-label="Main">
    @canany(\App\Support\PermissionCatalog::viewPermissionsOf('Overview'))
    <p class="nav-section">Overview</p>
    <div class="space-y-0.5">
        @can('dashboard.view')
        <x-side-navigation.link href="/dashboard" icon="fi-rr-dashboard" name="Dashboard"
            :active="request()->routeIs('dashboard')" />
        @endcan
        @can('dashboard.inventory.view')
        <x-side-navigation.link href="/dashboard/inventory" icon="fi-rr-chart-histogram" name="Inventory"
            :active="request()->routeIs('dashboard.inventory')" />
        @endcan
        @can('dashboard.distribution.view')
        <x-side-navigation.link href="/dashboard/distribution" icon="fi-rr-chart-connected" name="Distribution Insights"
            :active="request()->routeIs('dashboard.distribution')" />
        @endcan
    </div>
    @endcanany

    @canany(\App\Support\PermissionCatalog::viewPermissionsOf('Supply Chain'))
    <p class="nav-section">Supply Chain</p>
    <div class="space-y-0.5">
        @can('books.view')
        <x-side-navigation.link href="/book" icon="fi-rr-book" name="Books"
            :active="(request()->routeIs('book') | request()->routeIs('book.*'))" />
        @endcan
        @can('print-orders.view')
        <x-side-navigation.link href="/print-order" icon="fi-rr-print" name="Print Orders"
            :active="(request()->routeIs('print-order') | request()->routeIs('print-order.*'))" />
        @endcan
        @can('packages.view')
        <x-side-navigation.link href="/book-packages" icon="fi-rr-box" name="Packages"
            :active="(request()->routeIs('book-packages') | request()->routeIs('packages.*'))" />
        @endcan
        @can('warehouses.view')
        <x-side-navigation.link href="/warehouse" icon="fi-rr-home-location-alt" name="Warehouses"
            :active="request()->routeIs('warehouse')" />
        @endcan
    </div>
    @endcanany

    @canany(\App\Support\PermissionCatalog::viewPermissionsOf('Logistics'))
    <p class="nav-section">Logistics</p>
    <div class="space-y-0.5">
        @can('routes.view')
        <x-side-navigation.link href="/route" icon="fi-rr-chart-tree" name="Routes"
            :active="(request()->routeIs('route') | request()->routeIs('route.*'))" />
        @endcan
        @can('distributions.view')
        <x-side-navigation.link href="/distribution" icon="fi-rr-truck-side" name="Distribution"
            :active="(request()->routeIs('distribution') | request()->routeIs('distribution.*'))" />
        @endcan
        @can('trace.view')
        <x-side-navigation.link href="/trace" icon="fi-rr-search" name="Trace"
            :active="( request()->routeIs('trace') | request()->routeIs('distribution-details.*'))" />
        @endcan
    </div>
    @endcanany

    @canany(\App\Support\PermissionCatalog::viewPermissionsOf('Field Operations'))
    <p class="nav-section">Field Operations</p>
    <div class="space-y-0.5">
        @can('manual-tracking.view')
        <x-side-navigation.link href="/manual-tracking" icon="fi-rr-cursor-finger" name="Manual Tracking"
            :active="(request()->routeIs('manual-tracking') | request()->routeIs('manual-tracking.*'))" />
        @endcan
    </div>
    @endcanany

    @canany(\App\Support\PermissionCatalog::viewPermissionsOf('Administration'))
    <p class="nav-section">Administration</p>
    <div class="space-y-0.5">
        @can('organizations.view')
        <x-side-navigation.link href="/organization" icon="fi-rr-building" name="Organizations"
            :active="(request()->routeIs('organization') | request()->routeIs('organization.*'))" />
        @endcan
        @can('locations.view')
        <x-side-navigation.link href="/location" icon="fi-rr-map-marker-home" name="Locations"
            :active="(request()->routeIs('location') | request()->routeIs('location.*'))" />
        @endcan
        @can('users.view')
        <x-side-navigation.link href="{{ route('users.index') }}" icon="fi-rr-user" name="Users"
            :active="request()->routeIs('users.*')" />
        @endcan
        @can('roles.view')
        <x-side-navigation.link href="{{ route('roles.index') }}" icon="fi-rr-key" name="Roles & Permissions"
            :active="request()->routeIs('roles.*')" />
        @endcan
        @can('logs.view')
        <x-side-navigation.link href="/log-viewer" icon="fi-rr-info" name="System Logs"
            :active="request()->routeIs('log-viewer')" />
        @endcan
    </div>
    @endcanany
</nav>
