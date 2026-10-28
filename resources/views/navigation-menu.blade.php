<div x-data="{ open: false }">
    <nav
        class="sticky top-0 z-20 flex items-center h-16 gap-3 px-4 border-b sm:px-6 bg-white/80 backdrop-blur-md border-slate-200/80 dark:bg-slate-900/80 dark:border-slate-800">
        <button type="button" class="btn-ghost !px-2 md:hidden" @click="open = true">
            <span class="sr-only">Open menu</span>
            <i class="flex text-xl fi fi-rr-menu-burger"></i>
        </button>

        <div class="flex items-center min-w-0 gap-3 md:hidden">
            <img src="/logom.png" alt="" class="w-8 h-8">
            <span class="font-bold text-brand-900 dark:text-white">Track <span class="text-gold-500">&amp;</span> Trace</span>
        </div>

        <div class="flex-col hidden min-w-0 md:flex">
            <span class="text-xs font-medium text-slate-500 dark:text-slate-400">
                {{ now()->format('l, j F Y') }}
            </span>
            <span class="text-sm font-semibold truncate text-slate-800 dark:text-slate-100">
                Welcome back, {{ \Illuminate\Support\Str::of(Auth::user()->name)->before(' ') }}
            </span>
        </div>

        <div class="flex items-center gap-1 ml-auto sm:gap-2">
            <button type="button" @click="darkMode = !darkMode" class="btn-ghost !px-2.5"
                :aria-label="darkMode ? 'Switch to light mode' : 'Switch to dark mode'">
                <i x-show="!darkMode" class="flex text-lg fi fi-rr-moon"></i>
                <i x-show="darkMode" x-cloak class="flex text-lg fi fi-rr-sun text-gold-400"></i>
            </button>

            <span class="hidden w-px h-8 mx-1 sm:block bg-slate-200 dark:bg-slate-700"></span>

            <x-jet-dropdown align="right" width="48">
                <x-slot name="trigger">
                    <button type="button"
                        class="flex items-center gap-3 p-1 pr-2 transition rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800">
                        @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                        <img class="object-cover rounded-full w-9 h-9 ring-2 ring-brand-100 dark:ring-brand-900"
                            src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}" />
                        @else
                        <span
                            class="flex items-center justify-center text-sm font-bold text-white rounded-full w-9 h-9 bg-brand-600">
                            {{ \Illuminate\Support\Str::of(Auth::user()->name)->substr(0, 1)->upper() }}
                        </span>
                        @endif
                        <span class="flex-col hidden text-left sm:flex">
                            <span class="text-sm font-semibold leading-tight text-slate-800 dark:text-slate-100">
                                {{ Auth::user()->name }}
                            </span>
                            <span class="text-xs leading-tight text-slate-500 dark:text-slate-400">
                                {{ Auth::user()->email }}
                            </span>
                        </span>
                        <i class="hidden text-slate-400 sm:flex fi fi-rr-angle-small-down"></i>
                    </button>
                </x-slot>

                <x-slot name="content">
                    <div class="px-4 py-2 text-xs font-semibold tracking-wide uppercase text-slate-400">
                        {{ __('Manage Account') }}
                    </div>

                    <x-jet-dropdown-link href="{{ route('profile.show') }}">
                        <i class="flex fi fi-rr-user"></i>
                        {{ __('Profile') }}
                    </x-jet-dropdown-link>

                    <div class="my-1 border-t border-slate-100 dark:border-slate-700"></div>

                    <form method="POST" action="{{ route('logout') }}" x-data>
                        @csrf
                        <x-jet-dropdown-link href="{{ route('logout') }}" class="!text-rose-600 dark:!text-rose-400"
                            onclick="event.preventDefault(); this.closest('form').submit();">
                            <i class="flex fi fi-rr-sign-out"></i>
                            {{ __('Log Out') }}
                        </x-jet-dropdown-link>
                    </form>
                </x-slot>
            </x-jet-dropdown>
        </div>
    </nav>

    <x-side-navigation.mobile />
</div>
