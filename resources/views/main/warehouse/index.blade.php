<x-app-layout>
    <x-slot name="header">
        <h2>{{ __('Warehouse') }}</h2>
        <p class="page-subtitle">Warehouses and their current holdings.</p>
    </x-slot>

    <livewire:wearhouse.list-wearhouse>
</x-app-layout>