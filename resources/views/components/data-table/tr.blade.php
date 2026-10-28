<tr {{ $attributes->merge(
    ['class' => 'border-b border-slate-100 last:border-0 transition-colors hover:bg-brand-50/40 dark:border-slate-800 dark:hover:bg-slate-800/50']
    ) }}>
    {{ $slot }}
</tr>
