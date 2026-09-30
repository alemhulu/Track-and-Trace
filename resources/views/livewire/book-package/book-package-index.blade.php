@extends('main.book-package.index')

@section('content')
<div class="bg-white rounded-lg dark:bg-gray-800" wire:poll.60s>
    <x-stat.section class="" name="Packages Information">
        <x-stat.list value="{{ $total ?? '0' }}" text="Total Packages" />
        <x-stat.list value="{{ $sent ?? '0'}}" text="Total Packages Sent" />
        <x-stat.list value="{{ $received ?? '0' }}" text="Total Packages Received" />
        <x-stat.list value="{{ $available ?? '0'}}" text="Total Packages Available" />
        <x-stat.list value="{{ $status['label'] ?? 'Pending' }}" text="Current Status" />
    </x-stat.section>


    <x-form.section class="mx-8 border-b dark:border-gray-700" title="Available Packages Per Book"
        subtitle="All package information grouped by subject, with every grade shown once per subject" />

    <div class="divide-y dark:divide-gray-700">
        @foreach ($subjects as $subject)
        <x-stat.section name="{{ $subject['subject']['name'] ?? 'Unknown Subject' }}">
            @foreach ($subject['grades'] as $package)
            <x-stat.grade-list grade="{{ $package['grade']['name'] ?? 'Unknown Grade' }}" sent="{{ $package['sent']}}"
                received="{{ $package['received']}}" available="{{ $package['available']}}"
                status="{{ $package['status']['label'] ?? 'Pending' }}"
                statusType="{{ $package['status']['type'] ?? 'secondary' }}" />
            @endforeach
        </x-stat.section>
        @endforeach
    </div>
</div>
@endsection
