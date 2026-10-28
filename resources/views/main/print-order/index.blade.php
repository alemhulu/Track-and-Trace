<x-app-layout>
    <x-slot name="header">
        <h2>{{ __('Print Order') }}</h2>
        <p class="page-subtitle">Place print orders and follow them through production.</p>
    </x-slot>

    <div class="mb-6">
        <x-supply-chain-steps class="mb-3" />
        <x-page.nav col='2'>
            <x-page.nav-link class="bg-blue-500" title="List" link="print-order.list" icon="fi fi-rr-list" total="0" />
            <x-page.nav-link class="bg-blue-700" title="Order New Print" link="print-order.order" can="print-orders.create"
                icon="fi fi-rr-print" />
        </x-page.nav>

        <div class="mt-6  tab mb-6">
            @yield('content')
        </div>
    </div>
</x-app-layout>