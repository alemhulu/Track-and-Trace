@props(['title' => 'Details', 'label' => 'View details'])
{{-- Eye button that opens the default slot in a dialog. Pass a `trigger` slot to replace the button. --}}
<div x-data="{ open: false }" {{ $attributes->merge(['class' => 'inline-block']) }}>
    @if (isset($trigger))
    <span x-on:click="open = true">{{ $trigger }}</span>
    @else
    <button type="button" x-on:click="open = true" class="icon-btn-view" title="{{ $label }}" aria-label="{{ $label }}">
        <i class="flex fi fi-rr-eye"></i>
    </button>
    @endif

    <div x-show="open" x-cloak x-transition.opacity x-on:keydown.escape.window="open = false" x-on:click.self="open = false"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" role="dialog" aria-modal="true">
        <div class="w-full max-w-lg overflow-y-auto text-left whitespace-normal card max-h-[85vh]">
            <div class="flex items-start justify-between gap-3 px-5 py-4 border-b border-slate-100 dark:border-slate-800">
                <h3 class="card-title">{{ $title }}</h3>
                <button type="button" x-on:click="open = false" class="text-slate-400 hover:text-slate-700 dark:hover:text-slate-200" aria-label="Close">
                    <i class="flex fi fi-rr-cross-small text-lg"></i>
                </button>
            </div>
            <div class="px-5 py-4 text-sm break-words text-slate-700 dark:text-slate-300">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
