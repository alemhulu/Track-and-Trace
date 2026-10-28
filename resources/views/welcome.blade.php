<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#1e4ea2">
        <meta name="description" content="Track and Trace follows every Ministry of Education textbook from the printer to the classroom.">
        <link rel="icon" href="/logom.png">
        <title>{{ config('app.name', 'Track and Trace') }} · Ministry of Education</title>

        <x-fonts />
        <link href="/css/uicons.css" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>

    @php
    $primaryHref = auth()->check() ? route('dashboard') : route('login');
    $primaryLabel = auth()->check() ? __('Open dashboard') : __('Sign in');

    $stages = [
        ['icon' => 'fi-rr-print', 'title' => 'Printer', 'text' => 'Titles are registered and every carton is labelled with a scannable code at the press.'],
        ['icon' => 'fi-rr-building', 'title' => 'Central store', 'text' => 'Stock is received against the print order and any shortfall is flagged on arrival.'],
        ['icon' => 'fi-rr-truck-side', 'title' => 'Regional hubs', 'text' => 'Shipments carry waybills, drivers and expected dates. Hubs confirm what actually arrived.'],
        ['icon' => 'fi-rr-shuffle', 'title' => 'Zones & woredas', 'text' => 'Packages are sorted by scan. Misroutes are caught before the truck leaves.'],
        ['icon' => 'fi-rr-school', 'title' => 'Schools', 'text' => 'Books are issued to classes and students, and supervisors verify them on site.'],
    ];

    $features = [
        ['icon' => 'fi-rr-camera', 'title' => 'Scan-first handling', 'text' => 'Scan a book or package with any phone camera to pick its title, count copies and confirm custody.'],
        ['icon' => 'fi-rr-map-marker', 'title' => 'Live route visibility', 'text' => 'See where each shipment is, who holds it and when it is expected — from region down to school.'],
        ['icon' => 'fi-rr-exclamation', 'title' => 'Discrepancies surfaced', 'text' => 'Short, damaged or misrouted deliveries raise an alert automatically so they are resolved, not lost.'],
        ['icon' => 'fi-rr-smartphone', 'title' => 'Works offline', 'text' => 'The field app keeps working without signal and syncs receipts and visits once back online.'],
        ['icon' => 'fi-rr-chart-histogram', 'title' => 'Coverage reporting', 'text' => 'Compare books delivered against enrolment to find the schools and grades still waiting.'],
        ['icon' => 'fi-rr-shield-check', 'title' => 'Role-based access', 'text' => 'Federal, regional and school staff each see and act on exactly the part of the chain they own.'],
    ];
    @endphp

    <body class="font-sans antialiased bg-white text-slate-700">
        <span class="brand-stripe"></span>

        {{-- Top navigation --}}
        <header x-data="{ scrolled: false }" x-init="scrolled = window.scrollY > 8"
            @scroll.window="scrolled = window.scrollY > 8"
            :class="scrolled ? 'bg-white/90 shadow-sm backdrop-blur' : 'bg-transparent'"
            class="sticky top-0 z-40 transition">
            <nav class="flex items-center justify-between gap-4 px-4 py-3 mx-auto max-w-7xl sm:px-6 lg:px-8" aria-label="Global">
                <a href="{{ url('/') }}" class="flex items-center gap-3">
                    <img class="w-auto h-10 sm:h-12" src="/logom.png" alt="Ministry of Education logo">
                    <span class="leading-tight">
                        <span class="hidden sm:block text-[11px] font-bold tracking-wider uppercase text-slate-400">FDRE · Ministry of Education</span>
                        <span class="block text-lg font-extrabold text-brand-800">Track &amp; Trace</span>
                    </span>
                </a>

                <div class="hidden gap-8 text-sm font-bold md:flex text-slate-600">
                    <a href="#how-it-works" class="hover:text-brand-700">How it works</a>
                    <a href="#features" class="hover:text-brand-700">Features</a>
                    <a href="#field" class="hover:text-brand-700">Field app</a>
                </div>

                <div class="flex items-center gap-2">
                    @guest
                    @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="hidden btn-ghost sm:inline-flex">{{ __('Register') }}</a>
                    @endif
                    @endguest
                    <a href="{{ $primaryHref }}" class="btn-primary whitespace-nowrap">
                        <i class="flex fi {{ auth()->check() ? 'fi-rr-apps' : 'fi-rr-sign-in-alt' }}"></i>
                        {{ $primaryLabel }}
                    </a>
                </div>
            </nav>
        </header>

        <main>
            {{-- Hero --}}
            <section class="relative overflow-hidden -mt-[4.5rem] pt-[4.5rem]">
                <div class="absolute inset-0 -z-10 bg-gradient-to-b from-brand-50 via-white to-white"></div>
                <div class="absolute -z-10 -top-40 -right-40 w-[38rem] h-[38rem] rounded-full bg-brand-200/40 blur-3xl"></div>
                <div class="absolute -z-10 top-56 -left-40 w-[26rem] h-[26rem] rounded-full bg-gold-200/40 blur-3xl"></div>

                <div class="grid items-center gap-12 px-4 pt-12 pb-20 mx-auto max-w-7xl sm:px-6 lg:px-8 lg:grid-cols-2 lg:pt-20 lg:pb-28">
                    <div>
                        <span class="badge-gold">
                            <i class="flex fi fi-rr-book-alt"></i> National textbook distribution
                        </span>
                        <h1 class="mt-5 text-4xl font-black leading-[1.1] tracking-tight text-slate-900 sm:text-5xl lg:text-6xl">
                            Every textbook,
                            <span class="relative whitespace-nowrap text-brand-600">
                                traced
                                <svg class="absolute left-0 w-full h-3 -bottom-1 text-gold-400" viewBox="0 0 200 12" preserveAspectRatio="none" aria-hidden="true">
                                    <path d="M2 9c40-6 120-8 196-3" stroke="currentColor" stroke-width="4" fill="none" stroke-linecap="round" />
                                </svg>
                            </span>
                            from press to pupil.
                        </h1>
                        <p class="max-w-xl mt-6 text-lg leading-relaxed text-slate-600">
                            Track and Trace gives the Ministry, regions and schools one shared, real-time view of where
                            every book is — and who is responsible for it — on its way to the student who needs it.
                        </p>
                        <div class="flex flex-wrap items-center gap-3 mt-8">
                            <a href="{{ $primaryHref }}" class="btn-primary btn-lg shadow-brand">
                                {{ $primaryLabel }} <i class="flex fi fi-rr-arrow-right"></i>
                            </a>
                            <a href="#how-it-works" class="btn-secondary btn-lg">
                                <i class="flex fi fi-rr-play-alt"></i> See how it works
                            </a>
                        </div>
                        <ul class="flex flex-wrap mt-8 text-sm font-semibold gap-x-6 gap-y-2 text-slate-500">
                            <li class="flex items-center gap-2"><i class="flex fi fi-rr-check text-emerald-500"></i> QR &amp; barcode scanning</li>
                            <li class="flex items-center gap-2"><i class="flex fi fi-rr-check text-emerald-500"></i> Offline field app</li>
                            <li class="flex items-center gap-2"><i class="flex fi fi-rr-check text-emerald-500"></i> Amharic-ready</li>
                        </ul>
                    </div>

                    {{-- Product preview: an illustrative shipment journey --}}
                    <div class="relative">
                        <div class="absolute rotate-3 -inset-4 rounded-[2rem] bg-gradient-to-br from-brand-600 to-brand-800 opacity-90"></div>
                        <div class="relative p-6 bg-white shadow-2xl rounded-3xl ring-1 ring-slate-900/5">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="eyebrow">Shipment</p>
                                    <p class="mt-1 text-lg font-extrabold text-slate-900">Grade 10 · Biology</p>
                                    <p class="text-sm text-slate-500">Student textbooks &amp; teacher guides</p>
                                </div>
                                <span class="badge-info shrink-0 whitespace-nowrap"><span class="w-1.5 h-1.5 rounded-full bg-sky-500 animate-pulse"></span> In transit</span>
                            </div>

                            <ol class="relative mt-6 ml-3 space-y-5 border-l-2 border-dashed border-slate-200">
                                @foreach ([
                                    ['Printed & labelled', 'Printer', 'done'],
                                    ['Received at central store', 'Addis Ababa', 'done'],
                                    ['Dispatched to regional hub', 'Waybill attached', 'current'],
                                    ['Sorted for woredas', 'Awaiting arrival', 'todo'],
                                    ['Issued to students', 'School', 'todo'],
                                ] as [$label, $where, $state])
                                <li class="pl-6">
                                    <span @class([
                                        'absolute -left-[0.6875rem] flex items-center justify-center w-5 h-5 rounded-full ring-4 ring-white',
                                        'bg-emerald-500 text-white' => $state === 'done',
                                        'bg-brand-600 text-white' => $state === 'current',
                                        'bg-slate-200' => $state === 'todo',
                                    ])>
                                        @if ($state === 'done')<i class="flex text-[10px] fi fi-rr-check"></i>@elseif ($state === 'current')<span class="w-1.5 h-1.5 bg-white rounded-full"></span>@endif
                                    </span>
                                    <p @class(['text-sm font-bold', 'text-slate-900' => $state !== 'todo', 'text-slate-400' => $state === 'todo'])>{{ $label }}</p>
                                    <p class="text-xs text-slate-500">{{ $where }}</p>
                                </li>
                                @endforeach
                            </ol>

                            <div class="grid grid-cols-3 gap-3 pt-5 mt-6 border-t border-slate-100">
                                <div class="p-3 text-center rounded-xl bg-brand-50">
                                    <i class="flex justify-center text-brand-600 fi fi-rr-box"></i>
                                    <p class="mt-1 text-xs font-bold text-slate-700">Packages</p>
                                </div>
                                <div class="p-3 text-center rounded-xl bg-gold-50">
                                    <i class="flex justify-center text-gold-600 fi fi-rr-camera"></i>
                                    <p class="mt-1 text-xs font-bold text-slate-700">Scanned</p>
                                </div>
                                <div class="p-3 text-center rounded-xl bg-emerald-50">
                                    <i class="flex justify-center text-emerald-600 fi fi-rr-shield-check"></i>
                                    <p class="mt-1 text-xs font-bold text-slate-700">Verified</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {{-- How it works --}}
            <section id="how-it-works" class="py-20 scroll-mt-20 bg-slate-50 sm:py-24">
                <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div class="max-w-2xl mx-auto text-center">
                        <p class="text-sm font-extrabold tracking-wider uppercase text-brand-600">How it works</p>
                        <h2 class="mt-2 text-3xl font-black tracking-tight text-slate-900 sm:text-4xl">One chain of custody, five clear hand-offs</h2>
                        <p class="mt-4 text-lg text-slate-600">Each stage confirms what it received before passing books on, so gaps are visible the moment they happen.</p>
                    </div>

                    <ol class="grid gap-6 mt-14 sm:grid-cols-2 lg:grid-cols-5">
                        @foreach ($stages as $i => $stage)
                        <li class="relative p-6 transition bg-white border group rounded-2xl border-slate-200 hover:-translate-y-1 hover:shadow-card-hover">
                            <div class="flex items-center justify-between">
                                <span class="flex items-center justify-center w-12 h-12 text-xl text-white transition rounded-2xl bg-brand-600 group-hover:bg-gold-400 group-hover:text-brand-950">
                                    <i class="flex fi {{ $stage['icon'] }}"></i>
                                </span>
                                <span class="text-3xl font-black text-slate-100">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                            </div>
                            <h3 class="mt-5 text-lg font-extrabold text-slate-900">{{ $stage['title'] }}</h3>
                            <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $stage['text'] }}</p>
                            @if (! $loop->last)
                            <i class="absolute z-10 hidden text-lg -translate-y-1/2 lg:flex fi fi-rr-angle-small-right -right-5 top-1/2 text-brand-300" aria-hidden="true"></i>
                            @endif
                        </li>
                        @endforeach
                    </ol>
                </div>
            </section>

            {{-- Features --}}
            <section id="features" class="py-20 scroll-mt-20 sm:py-24">
                <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div class="grid gap-12 lg:grid-cols-3">
                        <div class="lg:sticky lg:top-28 lg:self-start">
                            <p class="text-sm font-extrabold tracking-wider uppercase text-brand-600">Features</p>
                            <h2 class="mt-2 text-3xl font-black tracking-tight text-slate-900 sm:text-4xl">Built for the people who move the books</h2>
                            <p class="mt-4 text-lg text-slate-600">From the federal warehouse to a rural school, every user gets the tools for their step — and nothing they don't need.</p>
                            <a href="{{ $primaryHref }}" class="mt-8 btn-primary">{{ $primaryLabel }} <i class="flex fi fi-rr-arrow-right"></i></a>
                        </div>
                        <div class="grid gap-5 sm:grid-cols-2 lg:col-span-2">
                            @foreach ($features as $feature)
                            <div class="p-6 transition border rounded-2xl border-slate-200 hover:border-brand-200 hover:shadow-card-hover">
                                <span class="flex items-center justify-center w-11 h-11 text-lg rounded-xl bg-brand-50 text-brand-600 ring-1 ring-inset ring-brand-100">
                                    <i class="flex fi {{ $feature['icon'] }}"></i>
                                </span>
                                <h3 class="mt-4 text-base font-extrabold text-slate-900">{{ $feature['title'] }}</h3>
                                <p class="mt-1.5 text-sm leading-relaxed text-slate-600">{{ $feature['text'] }}</p>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>

            {{-- Field app --}}
            <section id="field" class="px-4 pb-20 scroll-mt-20 sm:px-6 lg:px-8 sm:pb-24">
                <div class="relative grid items-center gap-10 px-6 py-12 mx-auto overflow-hidden max-w-7xl rounded-3xl bg-brand-900 sm:px-12 lg:grid-cols-2 lg:py-16">
                    <div class="absolute rounded-full -top-24 -right-24 w-80 h-80 bg-brand-600/40 blur-3xl"></div>
                    <div class="absolute rounded-full -bottom-24 -left-24 w-72 h-72 bg-gold-400/20 blur-3xl"></div>

                    <div class="relative">
                        <span class="text-white badge bg-white/10 ring-1 ring-inset ring-white/20">
                            <i class="flex fi fi-rr-smartphone"></i> Field app
                        </span>
                        <h2 class="mt-4 text-3xl font-black tracking-tight text-white sm:text-4xl">No signal? No problem.</h2>
                        <p class="mt-4 text-lg text-brand-100">
                            Download the day's shipments before heading out. Receive packages, issue books and record
                            school visits offline — everything syncs the next time you're connected.
                        </p>
                        <ul class="mt-6 space-y-3 text-brand-50">
                            <li class="flex items-start gap-3"><i class="flex mt-1 fi fi-rr-shield-check text-gold-300"></i> Camera scanning on any modern phone</li>
                            <li class="flex items-start gap-3"><i class="flex mt-1 fi fi-rr-shield-check text-gold-300"></i> Queued actions survive app restarts</li>
                            <li class="flex items-start gap-3"><i class="flex mt-1 fi fi-rr-shield-check text-gold-300"></i> Installable to the home screen</li>
                        </ul>
                    </div>

                    <div class="relative flex justify-center">
                        <div class="w-64 p-3 shadow-2xl bg-slate-900 rounded-[2.5rem] ring-1 ring-white/10">
                            <div class="overflow-hidden bg-slate-50 rounded-[2rem]">
                                <div class="flex items-center justify-between px-4 py-3 text-white bg-brand-600">
                                    <span class="text-xs font-bold">Field App</span>
                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-amber-300 text-amber-950">Offline</span>
                                </div>
                                <div class="p-4 space-y-3">
                                    <div class="flex items-center justify-center h-28 border-2 border-dashed rounded-xl border-brand-300 bg-brand-50">
                                        <i class="flex text-4xl fi fi-rr-camera text-brand-500"></i>
                                    </div>
                                    @foreach (['Package received', 'Package received', 'School visit recorded'] as $item)
                                    <div class="flex items-center gap-2 p-2.5 bg-white rounded-lg shadow-sm">
                                        <i class="flex text-sm fi fi-rr-time-quarter-past text-amber-500"></i>
                                        <span class="text-xs font-semibold text-slate-700">{{ $item }}</span>
                                    </div>
                                    @endforeach
                                    <div class="py-2 text-xs font-bold text-center text-white rounded-lg bg-brand-600/50">Sync 3 when online</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Closing call to action --}}
            <section class="px-4 pb-24 text-center sm:px-6 lg:px-8">
                <img src="/logom.png" alt="" class="w-auto h-16 mx-auto">
                <h2 class="max-w-2xl mx-auto mt-6 text-3xl font-black tracking-tight text-slate-900 sm:text-4xl">
                    Every student deserves their books on the first day of school.
                </h2>
                <p class="max-w-xl mx-auto mt-4 text-lg text-slate-600">Sign in with the account issued by your regional or federal office to get started.</p>
                <div class="flex flex-wrap justify-center gap-3 mt-8">
                    <a href="{{ $primaryHref }}" class="btn-primary btn-lg shadow-brand">{{ $primaryLabel }} <i class="flex fi fi-rr-arrow-right"></i></a>
                    @guest
                    @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="btn-ghost btn-lg">{{ __('Forgot password?') }}</a>
                    @endif
                    @endguest
                </div>
            </section>
        </main>

        <footer class="border-t border-slate-200 bg-slate-50">
            <div class="flex flex-col items-center justify-between gap-4 px-4 py-8 mx-auto text-sm max-w-7xl sm:flex-row sm:px-6 lg:px-8 text-slate-500">
                <p>&copy; {{ date('Y') }} Federal Democratic Republic of Ethiopia · Ministry of Education</p>
                <div class="flex gap-6 font-semibold">
                    @if (Route::has('terms.show'))
                    <a href="{{ route('terms.show') }}" class="hover:text-brand-700">Terms</a>
                    @endif
                    @if (Route::has('policy.show'))
                    <a href="{{ route('policy.show') }}" class="hover:text-brand-700">Privacy</a>
                    @endif
                </div>
            </div>
            <span class="brand-stripe"></span>
        </footer>
    </body>

</html>
