@props(['label', 'value', 'hint' => null, 'tone' => 'brand', 'icon' => null, 'delta' => null, 'compact' => false])
{{-- Headline metric tile. `tone` picks the icon colour; `delta` is ['label' => ..., 'direction' => up|down|flat]. --}}
@php
$iconTone = match ($tone) {
    'gold', 'amber' => 'bg-gold-50 text-gold-600 ring-gold-100 dark:bg-gold-900/30 dark:text-gold-300 dark:ring-gold-900',
    'emerald', 'green', 'teal' => 'bg-emerald-50 text-emerald-600 ring-emerald-100 dark:bg-emerald-900/30 dark:text-emerald-300 dark:ring-emerald-900',
    'rose', 'red', 'ember' => 'bg-rose-50 text-rose-600 ring-rose-100 dark:bg-rose-900/30 dark:text-rose-300 dark:ring-rose-900',
    'sky' => 'bg-sky-50 text-sky-600 ring-sky-100 dark:bg-sky-900/30 dark:text-sky-300 dark:ring-sky-900',
    'slate' => 'bg-slate-100 text-slate-600 ring-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:ring-slate-700',
    default => 'bg-brand-50 text-brand-600 ring-brand-100 dark:bg-brand-900/40 dark:text-brand-300 dark:ring-brand-900',
};
$direction = $delta['direction'] ?? 'flat';
$deltaClasses = match ($direction) {
    'up' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
    'down' => 'bg-rose-50 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300',
    default => 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400',
};
@endphp

<div {{ $attributes->merge(['class' => 'card p-5 transition hover:shadow-card-hover']) }}>
    <div class="flex items-start justify-between gap-3">
        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">{{ $label }}</p>
        @if ($icon)
        <span class="flex items-center justify-center w-10 h-10 rounded-xl ring-1 ring-inset {{ $iconTone }}">
            <i class="flex text-lg fi {{ $icon }}"></i>
        </span>
        @endif
    </div>
    <p @class(['mt-1 font-bold tracking-tight break-words text-slate-900 tabular-nums dark:text-white', 'text-3xl' => ! $compact, 'text-xl' => $compact])>{{ $value }}</p>
    @if ($hint)
    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $hint }}</p>
    @endif
    @if ($delta)
    <p class="inline-flex items-center gap-1 px-2 py-0.5 mt-3 text-xs font-semibold rounded-md {{ $deltaClasses }}">
        @if ($direction === 'up')<i class="flex fi fi-rr-arrow-small-up"></i>@elseif ($direction === 'down')<i class="flex fi fi-rr-arrow-small-down"></i>@endif
        {{ $delta['label'] ?? '' }}
    </p>
    @endif
    {{ $slot }}
</div>
