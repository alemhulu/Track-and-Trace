@props(['grade' => '1', 'received' => '00', 'sent' => '00', 'available' => '00', 'status' => 'Pending', 'statusType' =>
'secondary'])
@php
$statusClasses = [
'secondary' => 'badge-neutral',
'danger' => 'badge-danger',
'info' => 'badge-brand',
'warning' => 'badge-gold',
'success' => 'badge-success',
'primary' => 'badge-brand',
][$statusType ?? 'secondary'] ?? 'badge-neutral';
@endphp
<div {{ $attributes->merge([ 'class' => 'flex overflow-hidden border rounded-xl border-slate-200 bg-white dark:bg-slate-900 dark:border-slate-800']) }}>
    <div class="flex flex-col items-center justify-center w-20 shrink-0 bg-brand-50 dark:bg-brand-900/30">
        <span class="text-[11px] font-semibold tracking-wide uppercase text-brand-500 dark:text-brand-300">Grade</span>
        <span class="text-3xl font-bold text-brand-700 dark:text-brand-100">{{ $grade }}</span>
    </div>

    <dl class="flex-1 p-3 space-y-1.5 text-sm">
        <div class="flex justify-between gap-2">
            <dt class="text-slate-500 dark:text-slate-400">Received</dt>
            <dd class="font-semibold tabular-nums text-slate-900 dark:text-slate-100">{{ $received }}</dd>
        </div>
        <div class="flex justify-between gap-2">
            <dt class="text-slate-500 dark:text-slate-400">Sent</dt>
            <dd class="font-semibold tabular-nums text-slate-900 dark:text-slate-100">{{ $sent }}</dd>
        </div>
        <div class="flex justify-between gap-2">
            <dt class="text-slate-500 dark:text-slate-400">Available</dt>
            <dd class="font-semibold tabular-nums text-slate-900 dark:text-slate-100">{{ $available }}</dd>
        </div>
        <div class="pt-1">
            <span class="{{ $statusClasses }} capitalize">{{ $status }}</span>
        </div>
    </dl>
</div>
