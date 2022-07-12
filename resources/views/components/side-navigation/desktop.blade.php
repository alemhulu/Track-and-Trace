<div>
    <!-- The whole future lies in uncertainty: live immediately. - Seneca -->
    <!-- Static sidebar for desktop -->
    <div class="hidden md:flex md:w-64 md:flex-col md:fixed md:inset-y-0">
        <!-- Sidebar component, swap this element with another sidebar if you like -->
        <div class="flex flex-col flex-grow pt-4 bg-white overflow-y-auto">
            <div class="flex items-center flex-shrink-0 px-2 space-x-2">
                <x-jet-application-mark class="block h-20 w-auto" />
                <div class=" hidden sm:flex flex-col text-left">
                    <div class="font-medium text-xs text-gray-500">FDRE</div>
                    <div class="font-medium text-xs text-gray-500">Ministry of Education</div>
                    <div class="font-semibold text-gray-700 leading-tight">Track And Trace
                    </div>
                </div>
            </div>
            <div class="mt-5 flex-1 flex flex-col">
                <nav class="flex-1 px-2 pb-4 space-y-1">

                    <a href="/dashboard"
                        class="text-gray-600 hover:bg-blue-600 hover:text-white group flex items-center px-2 py-2 text-sm font-semibold rounded-md">
                        <i class="fi fi-rr-dashboard mr-3 flex text-2xl z-10 py-1"></i>
                        Dashboard
                    </a>

                    <a href="/location"
                        class="text-gray-600 hover:bg-blue-600 hover:text-white group flex items-center px-2 py-2 text-sm font-semibold rounded-md">
                        <i class="fi fi-rr-map-marker-home mr-3 flex text-2xl z-10 py-1"></i>
                        Location
                    </a>

                    <a href="/organization"
                        class="text-gray-600 hover:bg-blue-600 hover:text-white group flex items-center px-2 py-2 text-sm font-semibold rounded-md">
                        <i class="fi fi-rr-building mr-3 flex text-2xl z-10 py-1"></i>
                        Organization
                    </a>

                    <a href="/warehouse"
                        class="text-gray-600 hover:bg-blue-600 hover:text-white group flex items-center px-2 py-2 text-sm font-semibold rounded-md">
                        <i class="fi fi-rr-home-location-alt mr-3 flex text-2xl z-10 py-1"></i>
                        Warehouse
                    </a>

                    <a href="/book"
                        class="text-gray-600 hover:bg-blue-600 hover:text-white group flex items-center px-2 py-2 text-sm font-semibold rounded-md">
                        <i class="fi fi-rr-book mr-3 flex text-2xl z-10 py-1"></i>
                        Book
                    </a>

                    <a href="/print-order"
                        class="text-gray-600 hover:bg-blue-600 hover:text-white group flex items-center px-2 py-2 text-sm font-semibold rounded-md">
                        <i class="fi fi-rr-print mr-3 flex text-2xl z-10 py-1"></i>
                        Print Order
                    </a>

                    <a href="/route"
                        class="text-gray-600 hover:bg-blue-600 hover:text-white group flex items-center px-2 py-2 text-sm font-semibold rounded-md">
                        <i class="fi fi-rr-chart-tree mr-3 flex text-2xl z-10 py-1"></i>
                        Route
                    </a>

                    <a href="/distribution"
                        class="text-gray-600 hover:bg-blue-600 hover:text-white group flex items-center px-2 py-2 text-sm font-semibold rounded-md">
                        <i class="fi fi-rr-truck-side mr-3 flex text-2xl z-10 py-1"></i>
                        Distribution
                    </a>

                    <a href="/trace"
                        class="text-gray-600 hover:bg-blue-600 hover:text-white group flex items-center px-2 py-2 text-sm font-semibold rounded-md">
                        <i class="fi fi-rr-paw mr-3 flex text-2xl z-10 py-1"></i>
                        Trace
                    </a>

                </nav>
            </div>
        </div>
    </div>
</div>