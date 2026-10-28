<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{'darkMode': false}" x-init="
    darkMode = JSON.parse(localStorage.getItem('darkMode'));
    $watch('darkMode', value => localStorage.setItem('darkMode', JSON.stringify(value)))">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#1e4ea2">
        <link rel="icon" href="/logom.png">
        <title>{{ config('app.name', 'Track and Trace') }}</title>

        <!-- Fonts -->
        <x-fonts />

        <!-- Styles -->
        @livewireStyles
        <link href="/css/uicons.css" rel="stylesheet">
        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>

    <body :class="{'dark': darkMode === true}" class="font-sans antialiased">
        <x-jet-banner />

        <div class="min-h-screen bg-slate-50 dark:bg-slate-950">
            <x-side-navigation.desktop />

            <div class="flex flex-col flex-1 min-h-screen md:pl-64">
                @livewire('navigation-menu')

                <div class="flex-1 w-full px-4 py-6 mx-auto max-w-screen-2xl sm:px-6 lg:px-8">
                    @if (isset($header))
                    <header class="flex flex-wrap items-center justify-between gap-4 mb-6 page-header">
                        <div class="min-w-0">
                            {{ $header }}
                        </div>
                        @if (isset($actionButton))
                        <div class="flex flex-wrap items-center gap-3">
                            {{ $actionButton }}
                        </div>
                        @endif
                    </header>
                    @endif

                    <main class="space-y-4">
                        <x-validation-errors />
                        <x-session-messages />
                        {{ $slot }}
                    </main>
                </div>
            </div>
        </div>

        <x-toast.notification />
        @stack('modals')

        @livewireScripts
        <script src="/js/tabs.js"></script>
        <script src="/js/qr-scanner.js"></script>
    </body>

</html>
