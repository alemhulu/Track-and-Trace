<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#1e4ea2">
        <link rel="icon" href="/logom.png">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <x-fonts />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>

    <body class="font-sans antialiased text-slate-900">
        {{ $slot }}
    </body>

</html>
