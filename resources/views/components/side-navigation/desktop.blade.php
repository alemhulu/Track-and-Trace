<div>
    <!-- The whole future lies in uncertainty: live immediately. - Seneca -->
    <!-- Static sidebar for desktop -->
    <div class="hidden md:flex md:w-64 md:flex-col md:fixed md:inset-y-0">
        <!-- Sidebar component, swap this element with another sidebar if you like -->
        <div class="flex flex-col flex-grow pt-4 overflow-y-auto bg-white dark:bg-gray-900">
            <div class="flex items-center flex-shrink-0 px-2 space-x-4">
                <div class="block w-24 h-24 bg-center bg-cover bg-image-one dark:bg-image-two ">
                    <img src="/logom.png" alt="MoE logo" srcset="" {{ $attributes }}>
                </div>
                <div class="flex-col hidden text-left  sm:flex">
                    <div class="text-xs font-medium text-gray-500 dark:text-gray-300">FDRE</div>
                    <div class="text-xs font-medium text-gray-500 dark:text-gray-300">Ministry of Education</div>
                    <div class="font-semibold leading-tight text-gray-700 dark:text-gray-300">Track & Trace
                    </div>
                </div>
            </div>

            <div class="flex w-full h-2 mt-4">
                <div class="w-3/12 h-full bg-yellow-400"></div>
                <div class="w-4/12 h-full bg-red-600"></div>
                <div class="w-2/12 h-full bg-blue-600"></div>
                <div class="w-3/12 h-full bg-blue-700"></div>
            </div>

            <x-side-navigation.nav />

            <x-side-navigation.footer />
        </div>
    </div>
</div>
