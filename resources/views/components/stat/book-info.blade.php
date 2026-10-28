@props(['Grade' => '00', 'range' => '100000 - 100050'])
<div {{ $attributes->merge([ 'class' => '']) }}>
    <div class="flex flex-col text-center border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden">
        <div class="flex flex-row">
            <div
                class="w-12 shrink-0 bg-brand-50 dark:bg-brand-900/30 flex items-center justify-center text-brand-500 dark:text-brand-300">
                <i class="text-xl flex fi fi-rr-book"></i>
            </div>

            <div class="flex flex-col py-1 px-3 min-w-fit">
                <div class="text-left">
                    <x-jet-label class="text-xs text-gray-400 dark:text-gray-400" value="Grade" />
                    <span class="text-sm font-semibold text-slate-900 dark:text-slate-100 leading-none">
                        {{ $grade }}</span>
                </div>

                @if ($range)
                <div class="text-left pt-1">
                    <x-jet-label class="text-xs text-gray-400 dark:text-gray-400" value="QRs Range" />
                    <span class="text-sm font-semibold text-slate-900 dark:text-slate-100 leading-none tracking-wide">
                        {{ $range }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>