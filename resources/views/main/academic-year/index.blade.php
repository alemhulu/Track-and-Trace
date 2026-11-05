<x-app-layout>
    <x-slot name="header">
        <h2>{{ __('Academic Years') }}</h2>
        <p class="page-subtitle">The school years books are allocated and issued for. Every screen opens on the current year.</p>
    </x-slot>

    <div class="mb-6">
        @yield('content')
    </div>
</x-app-layout>
