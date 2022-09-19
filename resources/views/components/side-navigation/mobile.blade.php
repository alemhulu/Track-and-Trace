<div x-show="open" class="fixed inset-0 flex z-40 md:hidden"
    x-description="Off-canvas menu for mobile, show/hide based on off-canvas menu state." x-ref="dialog"
    aria-modal="true" style="display: none;">

    <div x-show="open" x-transition:enter="transition-opacity ease-linear duration-300"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-linear duration-300" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-600 bg-opacity-75"
        x-description="Off-canvas menu overlay, show/hide based on off-canvas menu state." @click="open = false"
        aria-hidden="true" style="display: none;">
    </div>

    <div x-show="open" x-transition:enter="transition ease-in-out duration-300 transform"
        x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in-out duration-300 transform" x-transition:leave-start="translate-x-0"
        x-transition:leave-end="-translate-x-full"
        x-description="Off-canvas menu, show/hide based on off-canvas menu state."
        class="relative flex-1 flex flex-col max-w-xs w-full" style="display: none;">

        <div x-show="open" x-transition:enter="ease-in-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="ease-in-out duration-300"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            x-description="Close button, show/hide based on off-canvas menu state."
            class="absolute top-0 right-0 -mr-12 pt-2" style="display: none;">
            <button type="button"
                class="ml-1 flex items-center justify-center h-10 w-10 rounded-full focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white"
                @click="open = false">
                <span class="sr-only">Close sidebar</span>
                <svg class="h-6 w-6 text-white" x-description="Heroicon name: outline/x"
                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                    aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>
        </div>

        <!-- Sidebar component, swap this element with another sidebar if you like -->
        <div class="flex flex-col flex-grow pt-4 bg-white overflow-y-auto">
            <div class="flex items-center flex-shrink-0 px-2 space-x-2">
                <x-jet-application-mark class="block h-20 w-auto" />
                <div class="flex flex-col text-left">
                    <div class="font-medium text-xs text-gray-500">FDRE</div>
                    <div class="font-medium text-xs text-gray-500">Ministry of Education</div>
                    <div class="font-semibold text-gray-700 leading-tight">Track And Trace
                    </div>
                </div>
            </div>
            <div class="mt-5 flex-1 flex flex-col">
                <nav class="flex-1 px-2 pb-4 space-y-1">
                    <x-side-navigation.link href="/dashboard" icon="fi-rr-dashboard" name="Dashboard"
                        :active="request()->routeIs('dashboard')" />

                    <x-side-navigation.link href="{{ route('users.index') }}" icon="fi-rr-user" name="Users"
                        :active="request()->routeIs('users')" />

                    <x-side-navigation.link href="{{ route('roles.index') }}" icon="fi-rr-key" name="Roles"
                        :active="request()->routeIs('roles')" />

                    <x-side-navigation.link href="/location" icon="fi-rr-map-marker-home" name="Location"
                        :active="request()->routeIs('location')" />

                    <x-side-navigation.link href="/organization" icon="fi-rr-building " name="Organization"
                        :active="request()->routeIs('organization')" />

                    <x-side-navigation.link href="/warehouse" icon="fi-rr-home-location-alt " name="Warehouse"
                        :active="request()->routeIs('warehouse')" />

                    <x-side-navigation.link href="/book" icon="fi-rr-book" name="Book"
                        :active="request()->routeIs('book')" />

                    <x-side-navigation.link href="/print-order" icon="fi-rr-print" name="Print Order"
                        :active="request()->routeIs('print-order')" />

                    <x-side-navigation.link href="/route" icon="fi-rr-chart-tree" name="Route"
                        :active="request()->routeIs('route')" />

                    <x-side-navigation.link href="/distribution" icon="fi-rr-truck-side" name="Distribution"
                        :active="request()->routeIs('distribution')" />

                    <x-side-navigation.link href="/trace" icon="fi-rr-paw" name="Trace"
                        :active="( request()->routeIs('trace') | request()->routeIs('distribution-details.*') )" />

                    @can('view-logs')
                    <x-side-navigation.link href="/log-viewer" icon="fi-rr-info" name="LOG"
                        :active="request()->routeIs('log-viewer')" />
                    @endcan

                </nav>
            </div>
        </div>
    </div>

    <div class="flex-shrink-0 w-14" aria-hidden="true">
        <!-- Dummy element to force sidebar to shrink to fit close icon -->
    </div>
</div>