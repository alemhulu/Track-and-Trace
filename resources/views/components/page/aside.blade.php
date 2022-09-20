@props(['title'])
{{-- Vertical Navigation Menu --}}
<aside class="py-6 px-5 sm:px-6 lg:py-0 lg:px-0 lg:col-span-3 bg-white dark:bg-gray-800 sm:rounded-md">
    <div class="border-b border-gray-200 mb-2 py-1 sm:px-4">
        <div class="flex justify-between items-center flex-wrap py-1">
            <header class="flex-shrink-0">
                <div class="py-2">
                    <h2 class="font-semibold text-lg text-lime-800 dark:text-lime-300">
                        {{ $title }}
                </div>
            </header>
            {{-- <x-alerts></x-alerts> --}}
        </div>
    </div>
    <nav class="space-y-3 px-5 py-5">
        {{ $slot }}
    </nav>
</aside>