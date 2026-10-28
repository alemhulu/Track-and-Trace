@props(['items' => []])
{{-- ['Label' => 'route.name' | null]; entries without a route render as plain text. --}}
<nav {{ $attributes->merge(['class' => 'flex']) }} aria-label="Breadcrumb">
    <ol class="flex flex-wrap items-center gap-1.5 text-xs font-semibold text-slate-500 dark:text-slate-400">
        <li>
            <a href="{{ route('dashboard') }}" class="flex items-center hover:text-brand-700 dark:hover:text-brand-300" aria-label="Dashboard">
                <i class="flex fi fi-rr-home"></i>
            </a>
        </li>
        @foreach ($items as $label => $route)
        <li class="flex items-center gap-1.5">
            <i class="flex text-[10px] fi fi-rr-angle-small-right text-slate-300 dark:text-slate-600"></i>
            @if ($route && ! $loop->last)
            <a href="{{ route($route) }}" class="hover:text-brand-700 dark:hover:text-brand-300">{{ $label }}</a>
            @else
            <span @if ($loop->last) aria-current="page" class="text-slate-700 dark:text-slate-200" @endif>{{ $label }}</span>
            @endif
        </li>
        @endforeach
    </ol>
</nav>
