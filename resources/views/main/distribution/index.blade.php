<x-app-layout>
    <x-slot name="header">
        <h2>{{ __('Distributions') }}</h2>
        <p class="page-subtitle">Plan and record book distributions along each route.</p>
    </x-slot>

    <div class="mb-6">
        <x-supply-chain-steps class="mb-3" />
        <x-page.nav col='2'>
            <x-page.nav-link class="bg-green-500" title="List" link="distribution.list" icon="fi fi-rr-list"
                total="0" />
            <x-page.nav-link class="bg-green-700" title="Add Distribution" link="distribution.add" can="distributions.create"
                icon="fi fi-rr-plus" />
        </x-page.nav>

        <div class="mt-6  tab mb-6">
            @yield('content')
        </div>
    </div>
</x-app-layout>