@props(['search' => false])
<div class="flex justify-between items-center flex-wrap py-3 border-b border-gray-200 sm:px-5 dark:border-gray-600">
    <header class="flex flex-shrink-0">
        <div class="mx-auto py-1">
            <p class="font-semibold text-xl text-lime-800 dark:text-lime-300">
                {{ $slot }}
            </p>
        </div>
    </header>

    <div class="{{ $search ? '' : 'hidden' }} w-full sm:max-w-xs">
        <label for="search" class="sr-only">Search</label>
        <div class="relative">
            <div class="pointer-events-none absolute inset-y-0 left-0 pl-3 flex items-center">
                <!-- Heroicon name: solid/search -->
                <svg class="h-6 w-6 text-gray-400 dark:text-gray-100" xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd"
                        d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                        clip-rule="evenodd" />
                </svg>
            </div>
            <input id="search" name="search" wire:model.debounce.500ms="search"
                class="block w-full bg-white dark:bg-gray-500 border border-gray-600 rounded-md py-2 pl-10 pr-3 text-sm placeholder-gray-200  dark:placeholder-gray-200 focus:outline-none focus:text-gray-900 dark:focus:text-gray-50 focus:placeholder-gray-400 dark:focus:placeholder-gray-400 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                placeholder="Search" type="search">
        </div>
    </div>

</div>