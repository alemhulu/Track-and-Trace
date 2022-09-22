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

            <x-side-navigation.nav />

            <x-side-navigation.footer />
        </div>
    </div>
</div>