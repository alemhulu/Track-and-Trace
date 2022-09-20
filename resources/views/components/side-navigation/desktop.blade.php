<div>
    <!-- The whole future lies in uncertainty: live immediately. - Seneca -->
    <!-- Static sidebar for desktop -->
    <div class="hidden md:flex md:w-64 md:flex-col md:fixed md:inset-y-0">
        <!-- Sidebar component, swap this element with another sidebar if you like -->
        <div class="flex flex-col flex-grow pt-4 bg-white overflow-y-auto dark:bg-gray-800">
            <div class="flex items-center flex-shrink-0 px-2 space-x-4">
                <div class="block h-24 bg-image-one dark:bg-image-two  bg-cover bg-center w-24 ">
                    {{-- <img src="/logom.png" alt="MoE logo" srcset="" {{ $attributes }}> --}}
                </div>
                <div class=" hidden sm:flex flex-col text-left">
                    <div class="font-medium text-xs text-gray-500 dark:text-gray-300">FDRE</div>
                    <div class="font-medium text-xs text-gray-500 dark:text-gray-300">Ministry of Education</div>
                    <div class="font-semibold text-gray-700 leading-tight dark:text-gray-300">Track & Trace
                    </div>
                </div>
            </div>

            <div class="w-full h-2 mt-4 flex">
                <div class="bg-yellow-400 h-full w-3/12"></div>
                <div class="bg-red-600 h-full w-4/12"></div>
                <div class="bg-blue-600 h-full w-2/12"></div>
                <div class="bg-blue-700 h-full w-3/12"></div>
            </div>

            <div class="mt-5 flex-1 flex flex-col">
                <nav class="flex-1 px-2 pb-4 space-y-1">
                    <x-side-navigation.link href="/dashboard" icon="fi-rr-dashboard" name="Dashboard"
                        :active="request()->routeIs('dashboard')" />

                    <x-side-navigation.link href="{{ route('users.index') }}" icon="fi-rr-user" name="Users"
                        :active="request()->routeIs('users.*')" />

                    <x-side-navigation.link href="{{ route('roles.index') }}" icon="fi-rr-key" name="Roles"
                        :active="request()->routeIs('roles.*')" />

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
                        :active="( request()->routeIs('trace') | request()->routeIs('distribution-details.*'))" />

                    @can('view-logs')
                    <x-side-navigation.link href="/log-viewer" icon="fi-rr-info" name="LOG"
                        :active="request()->routeIs('log-viewer')" />
                    @endcan

                </nav>
            </div>

            <div class="py-2 border-none horizontal-btn-active dark:bg-gray-700">
                <p class="text-xs text-blue-800/70 text-center tracking-wider dark:text-gray-400">Version: 2.23</p>
            </div>
        </div>
    </div>
</div>