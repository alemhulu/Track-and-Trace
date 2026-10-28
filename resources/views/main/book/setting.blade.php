@extends('main.book.index')
@section('content')
<div class="mt-6 mb-6 space-y-4">
    <div class="px-5 py-4 bg-white border border-gray-100 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Book Settings</h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Manage subject, grade, book type, print type, and paper size settings used throughout the book workflow.
        </p>
    </div>

    <div class="tab">
        <x-page.vTab>
            <x-page.aside title="Book Setting" sticky>
                @can('books.subjects.view')
                <x-page.tabLink name="Subject" link="Subject" />
                @endcan
                @can('books.grades.view')
                <x-page.tabLink name="Grade" link="Grade" />
                @endcan
                @can('books.types.view')
                <x-page.tabLink name="Book Type" link="BookType" />
                @endcan
                @can('books.print-types.view')
                <x-page.tabLink name="Print Type" link="PrintType" />
                @endcan
                @can('books.paper-sizes.view')
                <x-page.tabLink name="Paper Size" link="PaperSize" />
                @endcan
            </x-page.aside>

            @can('books.subjects.view')
            <x-page.vLinkPage link="Subject" component="book.subject.add-subject" />
            @endcan
            @can('books.grades.view')
            <x-page.vLinkPage link="Grade" component="book.grade.add-grade" />
            @endcan
            @can('books.types.view')
            <x-page.vLinkPage link="BookType" component="book.book-type.add-book-type" />
            @endcan
            @can('books.print-types.view')
            <x-page.vLinkPage link="PrintType" component="book.print-type.add-print-type" />
            @endcan
            @can('books.paper-sizes.view')
            <x-page.vLinkPage link="PaperSize" component="book.paper-size.add-paper-size" />
            @endcan
        </x-page.vTab>
    </div>
</div>
@endsection
