<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'Laravel') }}</title>
        <!-- Fonts -->
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>

    <body>
        <div class="font-sans text-gray-900 antialiased">
            <div class="relative w-full">
                <div class="mx-auto">
                    <div
                        class="relative flex flex-col justify-between z-10 bg-white/50 backdrop-blur backdrop-opacity-90 lg:max-w-2xl lg:w-full h-screen">

                        <div class="px-4 sm:px-6 lg:mx-6">
                            <nav class=" flex items-center justify-between lg:justify-start py-10" aria-label="Global">
                                <div class="flex items-center flex-grow flex-shrink-0 lg:flex-grow-0">
                                    <div class="flex items-center justify-between w-full md:w-auto">
                                        <a href="#">
                                            <span class="sr-only">Track and Trace</span>
                                            <img class="w-auto h-[6.5rem]" src="https://moe.gov.et/logom.png">
                                        </a>
                                        <div class="-mr-2 flex-0 items-center">
                                            <span
                                                class=" text-xs text-gray-400 leading-relaxed tracking-wide flex pl-3 -mb-6">
                                                FDRE
                                            </span>
                                            <span
                                                class="p-3 inline-flex items-center justify-center text-gray-500 text-xl leading-relaxed tracking-wide font-semibold">
                                                Ministry of Education</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="hidden md:block md:ml-10 md:pr-4 md:space-x-8">
                                    @if (Route::has('login'))
                                    @auth
                                    <a href="{{ url('/dashboard') }}"
                                        class="font-medium text-gray-500 hover:text-gray-900">dashboard</a>
                                    {{-- @else
                                        <a href="{{ route('login') }}"
                                    class="font-medium text-sky-600 hover:text-sky-500">Log in</a>
                                    @if (Route::has('register'))
                                    <a href="{{ route('register') }}"
                                        class="font-medium text-sky-600 hover:text-sky-500">Register</a>
                                    @endif --}}
                                    @endauth
                                    @endif
                                </div>
                            </nav>
                        </div>


                        <main class="mx-auto max-w-7xl my-auto">
                            <div class="flex flex-col sm:text-center lg:text-left justify-center mx-14">
                                <h1
                                    class="text-4xl tracking-tight font-extrabold text-gray-900 sm:text-5xl md:text-6xl">
                                    <span class="block xl:inline">MoE Books</span>
                                    <span class="block text-sky-600 xl:inline"> Track and Trace</span>
                                </h1>
                                <p
                                    class="mt-3 text-base text-gray-500 sm:mt-5 sm:text-lg sm:max-w-xl sm:mx-auto md:mt-5 md:text-xl lg:mx-0">
                                    NB: The purpose of T&T system is to allow users to monitor in real time the movement
                                    and locations of the book distribution starting from the printer all the way to the
                                    final or end users.
                                </p>
                                <div class="mt-5 sm:mt-8 sm:flex sm:justify-center lg:justify-start">
                                    <div class="rounded-md shadow">
                                        @if (Route::has('login'))
                                        @auth
                                        <a href="{{ route('dashboard') }}"
                                            class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-white bg-sky-600 hover:bg-sky-700 md:py-4 md:text-lg md:px-10">
                                            Dashboard
                                        </a>
                                        @else
                                        <a href="{{ route('login') }}"
                                            class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-white bg-sky-600 hover:bg-sky-700 md:py-4 md:text-lg md:px-10">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26"
                                                fill="currentColor" class="bi bi-box-arrow-in-right mr-3 text-xl"
                                                viewBox="0 0 16 16">
                                                <path fill-rule="evenodd"
                                                    d="M6 3.5a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-2a.5.5 0 0 0-1 0v2A1.5 1.5 0 0 0 6.5 14h8a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2h-8A1.5 1.5 0 0 0 5 3.5v2a.5.5 0 0 0 1 0v-2z" />
                                                <path fill-rule="evenodd"
                                                    d="M11.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 1 0-.708.708L10.293 7.5H1.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3z" />
                                            </svg>

                                            Log in
                                        </a>
                                        @endauth
                                        @endif
                                    </div>
                                    <div class="mt-3 sm:mt-0 sm:ml-3">
                                        <a href="/register"
                                            class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-sky-700 bg-sky-100 hover:bg-sky-200 md:py-4 md:text-lg md:px-10">
                                            Register
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </main>
                    </div>
                </div>
                <div class="absolute inset-y-72 lg:inset-y-0 right-0 lg:w-3/4 w-full">
                    <img class="h-96 w-full object-cover sm:h-72 md:h-96 lg:w-full lg:h-full"
                        src="https://augray.com/blog/wp-content/uploads/2020/12/augmented1-1.jpg" alt="">
                </div>
            </div>
        </div>
    </body>

</html>