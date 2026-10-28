@props(['name'])
{{-- Initials badge for a person. --}}
@php
$initials = collect(preg_split('/\s+/', trim($name)))->filter()->take(2)->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->implode('');
@endphp
<span {{ $attributes->merge(['class' => 'flex items-center justify-center w-9 h-9 text-xs font-extrabold rounded-full shrink-0 bg-brand-50 text-brand-700 ring-1 ring-inset ring-brand-100 dark:bg-brand-900/40 dark:text-brand-200 dark:ring-brand-800']) }}>
    {{ $initials ?: '?' }}
</span>
