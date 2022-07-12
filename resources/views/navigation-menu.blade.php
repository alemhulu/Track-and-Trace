<nav x-data="{ open: false }"
    class="sticky top-0 z-10 flex-shrink-0 flex bg-white border-b border-gray-100  shadow m-3 rounded-lg">
    <button type="button"
        class="px-4 border-r border-gray-200 text-gray-500 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500 md:hidden"
        @click="open = true">
        <span class="sr-only">Open sidebar</span>
        <svg class="h-6 w-6" x-description="Heroicon name: outline/menu-alt-2" xmlns="http://www.w3.org/2000/svg"
            fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path>
        </svg>
    </button>

    <!-- Primary Navigation Menu -->
    <div class="flex-1 flex justify-between px-4 sm:px-6 lg:px-8 h-16">
        <div class="flex">
            <!-- Navigation Links -->
            <div class="hidden space-x-8 sm:-my-px sm:flex">
                <x-jet-nav-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')">
                    {{ __('Dashboard') }}
                </x-jet-nav-link>
                <x-jet-nav-link href="{{ route('location') }}" :active="request()->routeIs('location')">
                    {{ __('Location') }}
                </x-jet-nav-link>
                <x-jet-nav-link href="{{ route('organization') }}" :active="request()->routeIs('organization')">
                    {{ __('Organization') }}
                </x-jet-nav-link>
                <x-jet-nav-link href="{{ route('warehouse') }}" :active="request()->routeIs('warehouse')">
                    {{ __('Warehouse') }}
                </x-jet-nav-link>
                <x-jet-nav-link href="{{ route('book') }}" :active="request()->routeIs('book')">
                    {{ __('Book') }}
                </x-jet-nav-link>
                <x-jet-nav-link href="{{ route('print-order') }}" :active="request()->routeIs('print-order')">
                    {{ __('Print Order') }}
                </x-jet-nav-link>
                <x-jet-nav-link href="{{ route('route') }}" :active="request()->routeIs('route')">
                    {{ __('Route') }}
                </x-jet-nav-link>
                <x-jet-nav-link href="{{ route('distribution') }}" :active="request()->routeIs('distribution')">
                    {{ __('Distribution') }}
                </x-jet-nav-link>
                <x-jet-nav-link href="{{ route('trace') }}" :active="request()->routeIs('trace')">
                    {{ __('Trace') }}
                </x-jet-nav-link>
            </div>
        </div>

        <div class="flex items-center sm:ml-6">

            <!-- Settings Dropdown -->
            <div class="ml-3 relative">
                <x-jet-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                        <button
                            class="flex text-sm border-2 border-transparent rounded-md sm:pr-1 focus:outline-none focus:border-gray-300 transition space-x-1 ">
                            <img class="flex h-10 w-10 rounded-md object-cover"
                                src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}" />
                            <div class=" hidden sm:flex flex-col text-left">
                                <div class="font-medium text-base text-gray-800 leading-tight">{{ Auth::user()->name }}
                                </div>
                                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
                            </div>
                        </button>

                        @else
                        <span class="inline-flex rounded-md">
                            <button type="button"
                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition">
                                {{ Auth::user()->name }}

                                <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            </button>
                        </span>
                        @endif
                    </x-slot>

                    <x-slot name="content">
                        <!-- Account Management -->
                        <div class="block px-4 py-2 text-xs text-gray-400">
                            {{ __('Manage Account') }}
                        </div>

                        <x-jet-dropdown-link href="{{ route('profile.show') }}">
                            <i class=" inline-flex fi fi-rr-user align-middle pb-1 text-md pr-2"></i>
                            {{ __('Profile') }}
                        </x-jet-dropdown-link>

                        <div class="border-t border-gray-100"></div>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}" x-data>
                            @csrf
                            <x-jet-dropdown-link href="{{ route('logout') }}" onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                <i class=" inline-flex fi fi-rr-sign-out align-middle pb-1 text-md pr-2"></i>
                                {{ __('Log Out') }}
                            </x-jet-dropdown-link>
                        </form>
                    </x-slot>
                </x-jet-dropdown>
            </div>
        </div>

    </div>

    <x-side-navigation.mobile />

</nav>