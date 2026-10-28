@props(['type' => 'info', 'title' => null, 'icon' => null])
{{-- Inline, non-dismissable message. type: info | success | warning | danger --}}
@php
$icon ??= [
    'success' => 'fi-rr-shield-check',
    'warning' => 'fi-rr-exclamation',
    'danger' => 'fi-rr-shield-exclamation',
][$type] ?? 'fi-rr-info';
@endphp
<div {{ $attributes->merge(['class' => 'alert-' . $type, 'role' => in_array($type, ['warning', 'danger']) ? 'alert' : 'status']) }}>
    <i class="flex mt-0.5 text-base fi {{ $icon }} shrink-0"></i>
    <div class="flex-1 min-w-0">
        @if ($title)
        <p class="font-bold">{{ $title }}</p>
        @endif
        <div @class(['mt-0.5' => $title])>{{ $slot }}</div>
    </div>
</div>
