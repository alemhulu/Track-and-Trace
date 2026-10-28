@extends('print.layout')

@section('title', 'Book issue sheet ' . $school->name)

@section('content')
<div class="head">
    <div>
        <h1>Textbook Issue Sheet</h1>
        <div>{{ $school->name }} · Grade {{ $grade->name }}{{ $section ? ' · Section ' . $section : '' }} · {{ $yearLabel }}</div>
        <div class="muted">Tick each book handed over and have the student (or parent) sign. ✓ = already holds the book. Enter the ticks in Issue Books or the field app.</div>
    </div>
    <div class="muted">Teacher: <span class="write"></span><br><br>Date: <span class="write"></span></div>
</div>

<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Student no.</th>
            <th>Name</th>
            <th>Sec.</th>
            @foreach ($titles as $title)
            <th>{{ \Illuminate\Support\Str::after($title->title, 'Grade ' . $grade->name . ' ') }}</th>
            @endforeach
            <th>Signature</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($students as $student)
        @php $held = $student->heldBooks->pluck('manual_book_id')->flip(); @endphp
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $student->student_no }}</td>
            <td>{{ $student->name }}</td>
            <td>{{ $student->section }}</td>
            @foreach ($titles as $title)
            <td class="box">{{ isset($held[$title->id]) ? '✓' : '☐' }}</td>
            @endforeach
            <td class="blank" style="min-width:110px"></td>
        </tr>
        @endforeach
        {{-- Space for students who are not registered yet --}}
        @for ($row = 0; $row < 5; $row++)
        <tr>
            <td>&nbsp;</td><td></td><td></td><td></td>
            @foreach ($titles as $title)
            <td class="box">☐</td>
            @endforeach
            <td></td>
        </tr>
        @endfor
    </tbody>
</table>

<div class="signatures">
    <div>Prepared by (name, signature)</div>
    <div>Checked by director (name, signature, stamp)</div>
    <div>Entered into the system by / date</div>
</div>
@endsection
