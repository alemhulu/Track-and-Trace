<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:text-gray-200">
            {{ __('Book') }}
        </h2>
    </x-slot>

    <div class="mb-6">
        <x-page.nav col='3'>
            <x-page.nav-link class="bg-yellow-500" title="List" tabLink="List" icon="fi fi-rr-book" total="0" />
            <x-page.nav-link class="bg-yellow-600" title="Add Book" tabLink="AddBook" icon="fi fi-rr-plus" />
            <x-page.nav-link class="bg-yellow-700" title="Book Settings" tabLink="BookSettings" icon="fi fi-rr-settings"
                total="5" />
        </x-page.nav>

        <div id="List" class="mt-6  tab mb-6" style="display:none; ">
            <livewire:book.list-book>
        </div>

        <div id="AddBook" class="mt-6  tab mb-6" style="display:none; ">
            <livewire:book.add-book>
        </div>

        <div id="BookSettings" class="mt-6  tab mb-6" style="display:none; ">
            <x-page.vTab>
                <x-page.aside title="Program Setting">
                    <x-page.tabLink name="Subject" link="Subject" />
                    <x-page.tabLink name="Grade" link="Grade" />
                    <x-page.tabLink name="Book Type" link="BookType" />
                    <x-page.tabLink name="Print Type" link="PrintType" />
                    <x-page.tabLink name="Paper Size" link="PaperSize" />
                </x-page.aside>

                <x-page.vLinkPage link="Subject" component="book.subject.add-subject" />
                <x-page.vLinkPage link="Grade" component="book.grade.add-grade" />
                <x-page.vLinkPage link="BookType" component="book.book-type.add-book-type" />
                <x-page.vLinkPage link="PrintType" component="book.print-type.add-print-type" />
                <x-page.vLinkPage link="PaperSize" component="book.paper-size.add-paper-size" />
            </x-page.vTab>
        </div>
    </div>
</x-app-layout>