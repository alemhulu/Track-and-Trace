<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:text-gray-200">
            {{ __('Location') }}
        </h2>
    </x-slot>

    <div class="mb-6">
        <x-page.nav col='4'>
            <livewire:count-location>
        </x-page.nav>

        <div class="mt-6  tab mb-6">
            @yield('content')
        </div>
    </div>
</x-app-layout>