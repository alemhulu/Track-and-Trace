<x-app-layout>
    <x-slot name="header">
        <h2>{{ __('Location') }}</h2>
        <p class="page-subtitle">Country, region, zone, woreda and kebele hierarchy.</p>
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