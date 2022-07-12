<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Track and Trace') }}</title>

        <!-- Fonts -->
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">

        <!-- Styles -->
        @livewireStyles
        <link href="/css/uicons.css" rel="stylesheet">
        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>

    <body class="font-sans antialiased">
        <x-jet-banner />

        <div class="min-h-screen bg-gray-100">
            <x-side-navigation.desktop />

            <div class="md:pl-64 flex flex-col flex-1">
                @livewire('navigation-menu')
                <!-- Page Heading -->
                @if (isset($header))
                <header class="bg-white shadow mx-3 rounded-lg">
                    <div class="mx-auto py-6 px-4 sm:px-6 lg:px-8 horizontal-btn-active rounded-lg">
                        {{ $header }}
                    </div>
                </header>
                <!-- Page Content -->
                <main class="m-3">
                    {{ $slot }}
                </main>
                @endif
            </div>
        </div>

        <x-toast.notification />
        @stack('modals')

        @livewireScripts
        <script src="/js/tabs.js"></script>
    </body>

</html>