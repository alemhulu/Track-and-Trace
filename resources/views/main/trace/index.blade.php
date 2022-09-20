<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:text-gray-200">
            {{ __('Trace') }}
        </h2>
    </x-slot>

    <div class="">
        <div class="w-auto">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <livewire:trace.trace-book-index>
            </div>
        </div>
    </div>
</x-app-layout>