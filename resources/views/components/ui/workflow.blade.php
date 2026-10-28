@props(['steps' => [], 'label' => 'Workflow'])
{{-- Numbered process strip that doubles as navigation.
     Each step: ['title', 'link' => route name, 'icon', 'hint', 'match' => route pattern(s), defaults to link*].
     The step matching the current route is highlighted and earlier steps show as done.
     A step with 'can' => permission stays visible but is not a link for users without it. --}}
@php
$current = collect($steps)->search(fn ($step) => request()->routeIs(...(array) ($step['match'] ?? $step['link'] . '*')));
@endphp

<nav {{ $attributes->merge(['class' => 'card p-2']) }} aria-label="{{ $label }}">
    <ol class="flex overflow-x-auto no-scrollbar">
        @foreach ($steps as $i => $step)
        @php
        $state = $current === false ? 'todo' : ($i < $current ? 'done' : ($i === $current ? 'current' : 'todo'));
        $locked = ! empty($step['can']) && ! auth()->user()?->canAny(explode('|', $step['can']));
        @endphp
        <li class="flex items-center flex-1 min-w-[9.5rem]">
            <{{ $locked ? 'div' : 'a' }} @unless ($locked) href="{{ route($step['link']) }}" @endunless
                @if ($state === 'current') aria-current="step" @endif
                @if ($locked) title="{{ __('You do not have access to this step') }}" aria-disabled="true" @endif
                @class([
                    'group flex items-center flex-1 gap-3 px-3 py-2.5 rounded-xl transition',
                    'bg-brand-600 shadow-brand' => $state === 'current',
                    'hover:bg-brand-50 dark:hover:bg-slate-800' => $state !== 'current' && ! $locked,
                    'opacity-50 cursor-not-allowed' => $locked,
                ])>
                <span @class([
                    'flex items-center justify-center shrink-0 w-9 h-9 rounded-full text-sm font-extrabold ring-1 ring-inset transition',
                    'bg-white/15 text-gold-300 ring-white/30' => $state === 'current',
                    'bg-emerald-50 text-emerald-600 ring-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-300 dark:ring-emerald-800' => $state === 'done',
                    'bg-slate-50 text-slate-500 ring-slate-200 group-hover:bg-white group-hover:text-brand-600 group-hover:ring-brand-200 dark:bg-slate-800 dark:text-slate-400 dark:ring-slate-700' => $state === 'todo',
                ])>
                    @if ($state === 'done')
                    <i class="flex fi fi-rr-check"></i>
                    @elseif (! empty($step['icon']))
                    <i class="flex fi {{ $step['icon'] }}"></i>
                    @else
                    {{ $i + 1 }}
                    @endif
                </span>
                <span class="min-w-0">
                    <span @class([
                        'block text-[10px] font-bold tracking-wider uppercase',
                        'text-brand-100' => $state === 'current',
                        'text-slate-400 dark:text-slate-500' => $state !== 'current',
                    ])>Step {{ $i + 1 }}</span>
                    <span @class([
                        'block text-sm font-bold truncate',
                        'text-white' => $state === 'current',
                        'text-slate-800 group-hover:text-brand-700 dark:text-slate-100' => $state !== 'current',
                    ])>{{ $step['title'] }}</span>
                    @if (! empty($step['hint']))
                    <span @class([
                        'hidden text-xs truncate 2xl:block',
                        'text-brand-100/90' => $state === 'current',
                        'text-slate-500 dark:text-slate-400' => $state !== 'current',
                    ])>{{ $step['hint'] }}</span>
                    @endif
                </span>
            </{{ $locked ? 'div' : 'a' }}>
            @if (! $loop->last)
            <i class="flex px-1 text-xs fi fi-rr-angle-small-right shrink-0 text-slate-300 dark:text-slate-600" aria-hidden="true"></i>
            @endif
        </li>
        @endforeach
    </ol>
</nav>
