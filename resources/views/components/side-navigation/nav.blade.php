<div class="mt-5 flex-1 flex flex-col">
    <nav class="flex-1 px-2 pb-4 space-y-1">
        <x-side-navigation.link href="/dashboard" icon="fi-rr-dashboard" name="Dashboard"
            :active="request()->routeIs('dashboard')" />

        @can('view-user')

        <x-side-navigation.link href="{{ route('users.index') }}" icon="fi-rr-user" name="Users"
            :active="request()->routeIs('users.*')" />

        @endcan

        @can('view-role')
        <x-side-navigation.link href="{{ route('roles.index') }}" icon="fi-rr-key" name="Roles"
            :active="request()->routeIs('roles.*')" />
        @endcan
        <x-side-navigation.link href="/location" icon="fi-rr-map-marker-home" name="Location"
            :active="(request()->routeIs('location') | request()->routeIs('location.*'))" />

        <x-side-navigation.link href=" /organization" icon="fi-rr-building " name="Organization"
            :active="(request()->routeIs('organization') | request()->routeIs('organization.*'))" />

        <x-side-navigation.link href="/print-order" icon="fi-rr-print" name="Print Order"
            :active="(request()->routeIs('print-order') | request()->routeIs('print-order.*'))" />

        <x-side-navigation.link href="/book-packages" icon="fi-rr-box" name="Packages"
            :active="(request()->routeIs('book-packages') | request()->routeIs('packages.*'))" />

        <x-side-navigation.link href="/warehouse" icon="fi-rr-home-location-alt " name="Warehouse"
            :active="request()->routeIs('warehouse')" />

        <x-side-navigation.link href="/book" icon="fi-rr-book" name="Book"
            :active="(request()->routeIs('book') | request()->routeIs('book.*'))" />

        <x-side-navigation.link href="/route" icon="fi-rr-chart-tree" name="Route"
            :active="(request()->routeIs('route') | request()->routeIs('route.*'))" />

        <x-side-navigation.link href="/distribution" icon="fi-rr-truck-side" name="Distribution"
            :active="(request()->routeIs('distribution') | request()->routeIs('distribution.*'))" />

        <x-side-navigation.link href="/trace" icon="fi-rr-paw" name="Trace"
            :active="( request()->routeIs('trace') | request()->routeIs('distribution-details.*'))" />

        @can('view-logs')
        <x-side-navigation.link href="/log-viewer" icon="fi-rr-info" name="LOG"
            :active="request()->routeIs('log-viewer')" />
        @endcan
    </nav>
</div>