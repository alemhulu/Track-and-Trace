@extends('main.book.index')
@section('content')
<div class="mt-6  tab mb-6">
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
@endsection