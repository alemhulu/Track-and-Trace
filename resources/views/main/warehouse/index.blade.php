<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:text-gray-200">
            {{ __('Warehouse') }}
        </h2>
    </x-slot>

    <livewire:wearhouse.list-wearhouse>
</x-app-layout>