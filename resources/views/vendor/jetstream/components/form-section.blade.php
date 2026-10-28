@props(['submit'])

<div {{ $attributes->merge(['class' => 'md:grid md:grid-cols-3 md:gap-6']) }}>
    <x-jet-section-title>
        <x-slot name="title">{{ $title }}</x-slot>
        <x-slot name="description">{{ $description }}</x-slot>
    </x-jet-section-title>

    <div class="mt-5 md:mt-0 md:col-span-2">
        <form wire:submit.prevent="{{ $submit }}">
            <div
                class="px-4 py-5 bg-white border border-slate-200/80 dark:border-slate-800 dark:bg-slate-900 sm:p-6 shadow-card {{ isset($actions) ? 'rounded-t-xl border-b-0' : 'rounded-xl' }}">
                <div class="grid grid-cols-6 gap-6">
                    {{ $form }}
                </div>
            </div>

            @if (isset($actions))
            <div
                class="flex items-center justify-end gap-3 px-4 py-3 border bg-slate-50 border-slate-200/80 dark:bg-slate-800/60 dark:border-slate-800 text-right sm:px-6 rounded-b-xl">
                {{ $actions }}
            </div>
            @endif
        </form>
    </div>
</div>