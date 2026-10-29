@props(['title' => 'Details'])
{{-- Table cell text that wraps and stops after three lines. When it is cut off, a View button shows all of it. --}}
<div x-data="{ open: false, clipped: false, measure() { this.clipped = this.$refs.text.scrollHeight > this.$refs.text.clientHeight + 1 } }"
    x-init="$nextTick(() => measure())" x-on:resize.window.debounce.200ms="measure()"
    {{ $attributes->merge(['class' => 'cell-wrap']) }}>
    <div x-ref="text" class="clamp-3">{{ $slot }}</div>
    <button type="button" x-show="clipped" x-cloak x-on:click="open = true" class="inline-flex items-center gap-1 mt-1 text-xs link">
        <i class="flex fi fi-rr-eye"></i> View all
    </button>

    <div x-show="open" x-cloak x-transition.opacity x-on:keydown.escape.window="open = false" x-on:click.self="open = false"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" role="dialog" aria-modal="true">
        <div class="w-full max-w-lg overflow-y-auto text-left card max-h-[85vh]">
            <div class="flex items-start justify-between gap-3 px-5 py-4 border-b border-slate-100 dark:border-slate-800">
                <h3 class="card-title">{{ $title }}</h3>
                <button type="button" x-on:click="open = false" class="text-slate-400 hover:text-slate-700 dark:hover:text-slate-200" aria-label="Close">
                    <i class="flex fi fi-rr-cross-small text-lg"></i>
                </button>
            </div>
            <div class="px-5 py-4 text-sm break-words whitespace-normal text-slate-700 dark:text-slate-300">{{ $slot }}</div>
        </div>
    </div>
</div>
