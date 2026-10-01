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
    class="fixed inset-0 z-50 visible transition-all ease-in-out bg-lime-900 bg-opacity-70 target:delay-75 target:duration-75 "
    style="display: none">

    <a href="#" class="absolute w-full h-full" wire:click='clearid'></a>

    <div x-show="show" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-90"
        class="fixed inset-0 {{ $maxWidth }} bg-white rounded-lg py-3 p-4 m-auto max-h-[90vh] overflow-hidden">
        <form wire:ignore.self wire:submit.prevent="submit">
            <aside class="flex flex-col h-full">
                {{-- Form Title --}}
                <div
                    class="sticky top-0 z-10 mb-4 bg-white border-b border-gray-200 dark:border-gray-700 sm:px-4 dark:bg-gray-800">
                    <div class="flex flex-wrap items-center justify-between py-1">
                        <header class="flex-shrink-0">
                            <div class="py-1 ">
                                <h2 class="text-lg font-semibold leading-tight text-lime-800">
                                    {{ $title }}
                                </h2>
                            </div>
                        </header>
                    </div>
                </div>

                {{-- Form Body --}}
                <div class="flex-1 px-4 pb-5 space-y-4 overflow-y-auto">
                    @isset($body)
                    {{ $body }}
                    @endisset
                </div>

                @if($buttons)
                {{-- Form Footer --}}
                <div
                    class="sticky bottom-0 z-10 px-4 py-3 text-right bg-white border-t border-gray-200 sm:px-6 dark:bg-gray-800 dark:border-gray-700">
                    <a href="#" wire:click='clearid'>
                        <x-jet-secondary-button class="">
                            @isset($type)
                            Close
                            @else
                            Cancel
                            @endisset
                        </x-jet-secondary-button>
                    </a>
                    <x-jet-button onclick="location.href='#';" type="submit" class="flex ml-4 disabled:opacity-40">
                        <x-action.button-loader wire:loading />
                        Save
                    </x-jet-button>
                </div>
                @endif
            </aside>
            {{-- <x-action.cardloader /> --}}
        </form>

        <a href="#" class="absolute top-4 right-4 text-lime-800" wire:click='clearid'>
            <button class="flex justify-center items-center px-1 p-1 transition
                delay-75 ease-out transform hover:-translate-y-0.5 hover:scale-105 gap-2  rounded-md
                border border-red-600 hover:bg-red-500 mx-1 border-opacity-30 hover:text-white
                active:bg-red-600 active:hover:scale-110 focus:scale-105 focus:-translate-y-0.5 focus:text-lime-900
                focus:bg-red-400">
                <i class="flex fi fi-rr-cross-small"></i>
            </button>
        </a>
    </div>
</div>
