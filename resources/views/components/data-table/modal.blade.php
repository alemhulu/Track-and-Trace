@props(['name', 'maxWidth','type', 'buttons' => true])

@php
$maxWidth = [
'sm' => 'sm:max-w-sm',
'md' => 'sm:max-w-md',
'lg' => 'sm:max-w-lg',
'xl' => 'sm:max-w-xl',
'2xl' => 'sm:max-w-2xl',
'3xl' => 'sm:max-w-3xl',
'4xl' => 'sm:max-w-4xl',
'5xl' => 'sm:max-w-5xl',
'6xl' => 'sm:max-w-6xl',
'7xl' => 'sm:max-w-7xl',
][$maxWidth ?? '2xl'];
@endphp

<div x-data="{ show: false }" x-show="show" @hashchange.window=" show = (location.hash === '#{{ $name }}');"
    class="fixed inset-0 z-50 visible transition-all ease-in-out bg-slate-900/60 backdrop-blur-sm"
    style="display: none">

    <a href="#" class="absolute w-full h-full" wire:click='clearid'></a>

    <div x-show="show" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-90"
        class="fixed inset-x-4 inset-y-0 sm:inset-0 {{ $maxWidth }} bg-white dark:bg-slate-900 rounded-2xl shadow-2xl m-auto h-fit max-h-[90vh] overflow-hidden flex flex-col ring-1 ring-slate-900/5 dark:ring-slate-700">
        <form wire:ignore.self wire:submit.prevent="submit" class="flex flex-col min-h-0 max-h-[90vh]">
            <aside class="flex flex-col min-h-0">
                {{-- Form Title --}}
                <div
                    class="sticky top-0 z-10 px-5 py-3 pr-14 bg-white border-b border-slate-100 dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex flex-wrap items-center justify-between py-1">
                        <header class="flex-shrink-0">
                            <div class="py-1 ">
                                <h2 class="text-lg font-semibold leading-tight text-slate-900 dark:text-white">
                                    {{ $title }}
                                </h2>
                            </div>
                        </header>
                    </div>
                </div>

                {{-- Form Body --}}
                <div class="flex-1 px-5 py-5 space-y-4 overflow-y-auto">
                    @isset($body)
                    {{ $body }}
                    @endisset
                </div>

                @if($buttons)
                {{-- Form Footer --}}
                <div
                    class="sticky bottom-0 z-10 flex justify-end gap-3 px-5 py-3 border-t bg-slate-50 border-slate-100 dark:bg-slate-800/60 dark:border-slate-800">
                    <a href="#" wire:click='clearid'>
                        <x-jet-secondary-button class="">
                            @isset($type)
                            Close
                            @else
                            Cancel
                            @endisset
                        </x-jet-secondary-button>
                    </a>
                    <x-jet-button onclick="location.href='#';" type="submit" class="disabled:opacity-40">
                        <x-action.button-loader wire:loading />
                        Save
                    </x-jet-button>
                </div>
                @endif
            </aside>
            {{-- <x-action.cardloader /> --}}
        </form>

        <a href="#" class="absolute z-20 top-3 right-3 btn-ghost !p-2 !rounded-full" wire:click='clearid' aria-label="Close">
            <i class="flex text-lg fi fi-rr-cross-small"></i>
        </a>
    </div>
</div>
