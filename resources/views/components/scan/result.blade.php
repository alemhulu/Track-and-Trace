@props(['scan'])
{{-- The last scan, large enough to read from arm's length while sorting --}}
@php
    $tone = match (true) {
        $scan->isAlert() => 'bg-red-600 text-white',
        (bool) $scan->flag => 'bg-amber-500 text-white',
        default => 'bg-green-600 text-white',
    };
@endphp
<div {{ $attributes->merge(['class' => "p-4 rounded-lg shadow-sm {$tone}"]) }}>
    @if ($scan->kind === \App\Models\ManualTracking\ManualScan::KIND_PACKAGE && $scan->zone)
    <p class="text-xs uppercase opacity-80">Package goes to</p>
    <p class="text-3xl font-bold">{{ $scan->zone->name }}</p>
    @elseif ($scan->kind === \App\Models\ManualTracking\ManualScan::KIND_BOOK)
    <p class="text-xs uppercase opacity-80">Title</p>
    <p class="text-2xl font-bold">{{ $scan->book->title ?? $scan->payload }}{{ $scan->edition === 'teacher' ? ' (teacher guide)' : '' }}</p>
    @else
    <p class="text-xs uppercase opacity-80">Scanned</p>
    <p class="text-2xl font-bold break-all">{{ $scan->payload }}</p>
    @endif
    @if ($scan->kind === \App\Models\ManualTracking\ManualScan::KIND_PACKAGE && $scan->book)
    <p class="text-sm opacity-90">{{ $scan->book->title }}</p>
    @endif
    @if ($scan->flag)
    <p class="mt-2 text-sm font-semibold">
        {{ \App\Models\ManualTracking\ManualScan::FLAG_LABELS[$scan->flag] ?? $scan->flag }}: {{ $scan->flag_message }}
        @if ($scan->isAlert()) An alert was sent to the misroutes list. @endif
    </p>
    @endif
</div>
