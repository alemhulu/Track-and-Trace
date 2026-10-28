@props(['batch' => '#PO1091479181', 'quantity' => '00', 'range' => '100001 - 100050'])
<div {{ $attributes->merge([ 'class' => '']) }}>
    <div class="flex overflow-hidden border rounded-xl border-slate-200 dark:border-slate-700">
        <div class="flex items-center justify-center w-12 shrink-0 bg-brand-50 text-brand-500 dark:bg-brand-900/30 dark:text-brand-300">
            <i class="flex text-xl fi fi-rr-box"></i>
        </div>

        <dl class="flex-1 px-3 py-2 space-y-1 text-sm">
            <div class="flex items-center justify-between gap-3">
                <dt class="text-xs text-slate-500 dark:text-slate-400">Batch</dt>
                <dd class="font-semibold text-slate-900 dark:text-slate-100">{{ $batch }}</dd>
            </div>

            @if ($range)
            <div class="flex items-center justify-between gap-3">
                <dt class="text-xs text-slate-500 dark:text-slate-400">QR range</dt>
                <dd class="font-mono text-xs text-slate-700 dark:text-slate-300">{{ $range }}</dd>
            </div>
            @endif

            <div class="flex items-center justify-between gap-3">
                <dt class="text-xs text-slate-500 dark:text-slate-400">Quantity</dt>
                <dd><span class="badge-brand tabular-nums">{{ $quantity }}</span></dd>
            </div>
        </dl>
    </div>
</div>
