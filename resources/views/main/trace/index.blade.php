<x-app-layout>
    <x-slot name="header">
        <h2>{{ __('Trace') }}</h2>
        <p class="page-subtitle">Look up any book and see where it has travelled.</p>
    </x-slot>

    <div class="">
        <div class="w-auto">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <livewire:trace.trace-book-index>
            </div>
        </div>
    </div>
</x-app-layout>