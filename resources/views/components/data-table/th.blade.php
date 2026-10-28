<th {{ $attributes ->merge(['class' => 'px-5 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase
    tracking-wider whitespace-nowrap select-none'])
    }}>
    <div class="flex items-center gap-1.5 {{ $attributes->has('id') ? 'cursor-pointer hover:text-brand-700 dark:hover:text-brand-300' : '' }}">
        {{ $slot }}

        @if ($attributes->has('id'))
        <span class="flex flex-col text-[9px] leading-[6px] text-slate-300 dark:text-slate-600">
            <i class="flex fi fi-rr-caret-up upcart"></i>
            <i class="flex fi fi-rr-caret-down upcart"></i>
        </span>
        @endif
    </div>
</th>
