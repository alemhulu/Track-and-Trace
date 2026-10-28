@props(['image', 'grade', 'subject', 'type', 'edition', 'ISBN'])
<div class="flex flex-row items-center gap-3">
    {{-- The icon shows through when the cover image is missing --}}
    <div class="relative flex items-center justify-center w-20 h-28 overflow-hidden rounded-lg shrink-0 bg-brand-50 text-brand-300 ring-1 ring-inset ring-brand-100 dark:bg-brand-900/30 dark:ring-brand-900">
        <i class="flex text-3xl fi fi-rr-book"></i>
        <div class="absolute inset-0 bg-center bg-cover" style="background-image: url('{{ $image }}')"></div>
    </div>
    <div class="flex flex-col">
        <div class="text-xs font-semibold tracking-wide uppercase text-slate-500 dark:text-slate-400">
            {{ $grade }}
        </div>
        <div class="text-base font-bold text-brand-700 dark:text-brand-200">
            {{ $subject }}
        </div>
        <div class="text-sm text-slate-600 dark:text-slate-300">
            {{ $type }}
        </div>
        <div class="text-sm text-slate-600 dark:text-slate-300 ordinal">
            {{ $edition }}
        </div>
        <div class="text-xs text-slate-500 dark:text-slate-400 tabular-nums">
            ISBN: {{ $ISBN }}
        </div>
    </div>
</div>