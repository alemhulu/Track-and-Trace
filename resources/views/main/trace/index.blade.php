<x-app-layout>
    <x-slot name="header">
        <h2>{{ __('Trace') }}</h2>
        <p class="page-subtitle">Look up any book and see where it has travelled.</p>
    </x-slot>

    <x-supply-chain-steps class="mb-6" />

    <div class="overflow-hidden card">
        <livewire:trace.trace-book-index>
    </div>
</x-app-layout>
