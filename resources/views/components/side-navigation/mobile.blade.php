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
        <div class="flex flex-col flex-grow pt-4 bg-white dark:bg-gray-800 overflow-y-auto">
            <div class="flex items-center flex-shrink-0 space-x-4 justify-center">
                <div class="block h-20 bg-image-one dark:bg-image-two  bg-cover bg-center w-20 ">
                    {{-- <img src="/logom.png" alt="MoE logo" srcset="" {{ $attributes }}> --}}
                </div>
                <div class=" sm:hidden flex-col text-left">
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

            <x-side-navigation.nav />

            <x-side-navigation.footer />
        </div>
    </div>

    <div class="flex-shrink-0 w-14" aria-hidden="true">
        <!-- Dummy element to force sidebar to shrink to fit close icon -->
    </div>
</div>