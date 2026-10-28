<x-app-layout>
    <x-slot name="header">
        <h2>{{ __('Book') }}</h2>
        <p class="page-subtitle">Catalogue of titles, editions and print specifications.</p>
    </x-slot>

    <div class="mb-6">
        <x-page.nav col='3'>
            <x-page.nav-link class="bg-yellow-500" title="List" link="book.list" icon="fi fi-rr-book" total="0" />
            <x-page.nav-link class="bg-yellow-600" title="Add Book" link="book.add" can="books.create" icon="fi fi-rr-plus" />
            <x-page.nav-link class="bg-yellow-700" title="Book Settings" link="book.setting" can="books.subjects.view|books.grades.view|books.types.view|books.print-types.view|books.paper-sizes.view" icon="fi fi-rr-settings"
                total="5" />
        </x-page.nav>

        <div class="mt-6  tab mb-6">
            @yield('content')
        </div>
    </div>
</x-app-layout>