<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:text-gray-200">
            {{ __('Book') }}
        </h2>
    </x-slot>

    <div class="mb-6">
        <x-page.nav col='3'>
            <x-page.nav-link class="bg-yellow-500" title="List" link="book.list" icon="fi fi-rr-book" total="0" />
            <x-page.nav-link class="bg-yellow-600" title="Add Book" link="book.add" icon="fi fi-rr-plus" />
            <x-page.nav-link class="bg-yellow-700" title="Book Settings" link="book.setting" icon="fi fi-rr-settings"
                total="5" />
        </x-page.nav>

        <div class="mt-6  tab mb-6">
            @yield('content')
        </div>
    </div>
</x-app-layout>