@props(['title', 'description' => null])
{{-- One block of a long form: explanation on the left, fields on the right (stacked on small screens). --}}
<div {{ $attributes->merge(['class' => 'grid gap-4 py-6 first:pt-0 last:pb-0 lg:grid-cols-3 lg:gap-8']) }}>
    <div>
        <h3 class="text-sm font-bold text-slate-900 dark:text-white">{{ $title }}</h3>
        @if ($description)
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $description }}</p>
        @endif
    </div>
    <div class="lg:col-span-2">
        {{ $slot }}
    </div>
</div>
