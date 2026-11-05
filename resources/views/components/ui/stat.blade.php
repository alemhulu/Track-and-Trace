@props(['label', 'value', 'hint' => null, 'icon' => null, 'tone' => 'brand', 'percent' => null, 'active' => false])
{{-- Small KPI tile. `percent` draws a progress bar under the value; `active` marks the tile
     as the filter currently applied when tiles double as filter buttons. --}}
@php
$tones = [
    'brand' => ['icon' => 'bg-brand-50 text-brand-600 dark:bg-brand-900/40 dark:text-brand-300', 'bar' => 'bg-brand-500'],
    'success' => ['icon' => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-900/40 dark:text-emerald-300', 'bar' => 'bg-emerald-500'],
    'warning' => ['icon' => 'bg-amber-50 text-amber-600 dark:bg-amber-900/40 dark:text-amber-300', 'bar' => 'bg-amber-500'],
    'danger' => ['icon' => 'bg-rose-50 text-rose-600 dark:bg-rose-900/40 dark:text-rose-300', 'bar' => 'bg-rose-500'],
    'info' => ['icon' => 'bg-sky-50 text-sky-600 dark:bg-sky-900/40 dark:text-sky-300', 'bar' => 'bg-sky-500'],
    'neutral' => ['icon' => 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300', 'bar' => 'bg-slate-500'],
];
$palette = $tones[$tone] ?? $tones['brand'];
$tag = $attributes->has('wire:click') || $attributes->has('x-on:click') ? 'button' : 'div';
@endphp
<{{ $tag }} @if ($tag === 'button') type="button" @endif
    {{ $attributes->class([
        'card p-4 text-left min-w-0',
        'transition hover:border-brand-300 dark:hover:border-brand-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500' => $tag === 'button',
        'ring-2 ring-brand-500 border-transparent' => $active,
    ]) }}>
    <div class="flex items-start justify-between gap-3">
        <p class="text-xs font-medium tracking-wide uppercase text-slate-500 dark:text-slate-400">{{ $label }}</p>
        @if ($icon)
        <span class="flex items-center justify-center w-8 h-8 rounded-lg shrink-0 {{ $palette['icon'] }}">
            <i class="flex fi {{ $icon }}"></i>
        </span>
        @endif
    </div>
    <p class="mt-1 text-2xl font-bold tabular-nums text-slate-900 dark:text-white">{{ $value }}</p>
    @if ($percent !== null)
    <div class="h-1.5 mt-2 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
        <div class="h-1.5 rounded-full {{ $palette['bar'] }}" style="width: {{ max(0, min(100, $percent)) }}%"></div>
    </div>
    @endif
    @if ($hint || $slot->isNotEmpty())
    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $hint }}{{ $slot }}</p>
    @endif
</{{ $tag }}>
