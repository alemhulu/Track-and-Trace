{{-- Off-canvas sidebar for small screens; `open` comes from the top bar's x-data --}}
<div x-show="open" x-cloak class="fixed inset-0 z-40 flex md:hidden" role="dialog" aria-modal="true"
    @keydown.escape.window="open = false">
    <div x-show="open" x-transition.opacity.duration.200ms class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"
        @click="open = false" aria-hidden="true"></div>

    <div x-show="open" x-transition:enter="transition ease-out duration-200 transform"
        x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-150 transform" x-transition:leave-start="translate-x-0"
        x-transition:leave-end="-translate-x-full" class="relative flex flex-col w-full max-w-[17rem]">
        <button type="button" @click="open = false"
            class="absolute top-4 -right-12 flex items-center justify-center w-9 h-9 rounded-full bg-white/10 text-white hover:bg-white/20">
            <span class="sr-only">Close menu</span>
            <i class="flex fi fi-rr-cross-small text-xl"></i>
        </button>
        <x-side-navigation.panel />
    </div>
</div>
