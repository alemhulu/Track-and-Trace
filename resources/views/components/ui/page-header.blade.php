@props(['title', 'subtitle' => null, 'icon' => null, 'breadcrumbs' => []])
{{-- In-page header for screens rendered outside the layout's header slot.
     `breadcrumbs` is ['Label' => 'route.name' | null]; the last crumb is the current page. --}}
<div {{ $attributes->merge(['class' => 'flex flex-wrap items-end justify-between gap-4']) }}>
    <div class="min-w-0">
        @if ($breadcrumbs)
        <x-ui.breadcrumbs :items="$breadcrumbs" class="mb-2" />
        @endif
        <div class="flex items-center gap-3">
            @if ($icon)
            <span class="flex items-center justify-center w-10 h-10 rounded-xl bg-brand-50 text-brand-600 ring-1 ring-inset ring-brand-100 dark:bg-brand-900/40 dark:text-brand-300 dark:ring-brand-900">
                <i class="flex text-lg fi {{ $icon }}"></i>
            </span>
            @endif
            <div class="min-w-0">
                <h2 class="text-xl font-extrabold tracking-tight truncate text-slate-900 dark:text-white">{{ $title }}</h2>
                @if ($subtitle)
                <p class="section-subtitle">{{ $subtitle }}</p>
                @endif
            </div>
        </div>
    </div>
    @if (isset($actions))
    <div class="flex flex-wrap items-center gap-2">
        {{ $actions }}
    </div>
    @endif
</div>
