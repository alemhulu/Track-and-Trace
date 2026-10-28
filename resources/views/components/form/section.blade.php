@props(['title' => 'Title', 'subtitle' => 'Subtitle'])
<div {{ $attributes->merge([ 'class' => 'pb-3 border-b border-slate-100 dark:border-slate-800']) }}>
    <h3 class="text-base font-semibold text-slate-900 dark:text-slate-50">
        {{ $title }}
    </h3>
    <p class="max-w-2xl mt-1 text-sm text-slate-500 dark:text-slate-400">
        {{ $subtitle }}
    </p>
</div>
