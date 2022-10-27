<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{'darkMode': false}" x-init="
    darkMode = JSON.parse(localStorage.getItem('darkMode'));
    $watch('darkMode', value => localStorage.setItem('darkMode', JSON.stringify(value)))">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <link rel="icon" class="bg-gray-100" href="https://moe.gov.et/logom.png">
        <title>{{ config('app.name', 'Track and Trace') }}</title>

        <!-- Fonts -->
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">

        <!-- Styles -->
        @livewireStyles
        <link href="/css/uicons.css" rel="stylesheet">
        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>

    <body :class="{'dark': darkMode === true}" class="font-sans antialiased">
        <x-jet-banner />

        <div class="min-h-screen bg-[#edf1f7] dark:bg-gray-900">
            <x-side-navigation.desktop />

            <div class="md:pl-64 flex flex-col flex-1">
                @livewire('navigation-menu')
                <!-- Page Heading -->
                @if (isset($header))
                <header class="bg-white dark:bg-gray-800 shadow mx-3 rounded-lg">
                    <div class="mx-auto py-6 px-4 sm:px-6 lg:px-8 rounded-lg flex justify-between items-center">
                        {{ $header }}
                        @if (isset($actionButton))
                        <div class="flex justify-between gap-3">
                            {{ $actionButton }}
                        </div>
                        @endif
                    </div>
                </header>
                <!-- Page Content -->
                <main class="m-3">
                    <x-validation-errors />
                    <x-session-messages />
                    {{ $slot }}
                </main>
                @endif
            </div>
        </div>

        <x-toast.notification />
        @stack('modals')

        @livewireScripts
        <script src="/js/tabs.js"></script>

        <script src="https://cdn.jsdelivr.net/gh/livewire/turbolinks@v0.1.x/dist/livewire-turbolinks.js"
            data-turbolinks-eval="false" data-turbo-eval="false"></script>
    </body>
    </body>

</html>