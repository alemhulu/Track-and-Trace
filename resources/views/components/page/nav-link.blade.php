@props(['title', 'total' => null, 'tabLink' => '', 'icon' => '', 'link' => '#', 'can' => null])
{{-- The per-link colour class callers pass is ignored on purpose: every module uses the brand palette.
     `can` hides the link from users without that permission ("a|b" for any of several). --}}
@if (! $can || auth()->user()?->canAny(explode('|', $can)))
@php
$active = $link != '#' && request()->routeIs($link . '*');
$href = $link != '#' ? route($link) : null;
@endphp

<li class="shrink-0">
    <a @if ($href) href="{{ $href }}" @else href="#" onclick="event.preventDefault(); openTab(event,'{{ $tabLink }}')" @endif
        @if ($active) aria-current="page" @endif
        @class([
            'group flex items-center gap-2.5 px-3.5 py-2 rounded-lg text-sm font-medium whitespace-nowrap transition',
            'bg-brand-600 text-white shadow-brand' => $active,
            'text-slate-600 hover:bg-brand-50 hover:text-brand-700 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white' => ! $active,
        ])>
        @if ($icon)
        <i @class([
            "flex text-base $icon",
            'text-gold-300' => $active,
            'text-brand-400 group-hover:text-brand-600 dark:text-brand-300' => ! $active,
        ])></i>
        @endif
        <span>{{ $title }}</span>
        @if (! is_null($total) && (int) $total > 0)
        <span @class([
            'px-1.5 py-0.5 text-[11px] font-semibold rounded-md leading-none',
            'bg-white/20 text-white' => $active,
            'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300' => ! $active,
        ])>{{ $total }}</span>
        @endif
    </a>
</li>
@endif
