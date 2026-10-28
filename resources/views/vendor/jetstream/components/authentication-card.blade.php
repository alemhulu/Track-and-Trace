<div class="flex min-h-screen bg-white">
    {{-- Brand panel --}}
    <div class="relative hidden w-1/2 overflow-hidden lg:flex bg-brand-900">
        <div class="absolute inset-0 bg-gradient-to-br from-brand-700 via-brand-900 to-brand-950"></div>
        <div class="absolute rounded-full -top-24 -right-24 w-96 h-96 bg-brand-500/30 blur-3xl"></div>
        <div class="absolute rounded-full -bottom-32 -left-16 w-[28rem] h-[28rem] bg-gold-400/20 blur-3xl"></div>
        <svg class="absolute inset-0 w-full h-full opacity-[0.07]" aria-hidden="true">
            <defs>
                <pattern id="auth-grid" width="32" height="32" patternUnits="userSpaceOnUse">
                    <path d="M32 0H0v32" fill="none" stroke="white" stroke-width="1" />
                </pattern>
            </defs>
            <rect width="100%" height="100%" fill="url(#auth-grid)" />
        </svg>

        <div class="relative flex flex-col justify-between w-full p-12 text-white">
            <div class="flex items-center gap-3">
                <span class="flex items-center justify-center w-12 h-12 bg-white rounded-full">
                    <img src="/logom.png" alt="" class="w-11 h-11">
                </span>
                <span class="text-sm font-semibold tracking-wide uppercase text-brand-100">
                    Federal Democratic Republic of Ethiopia<br>Ministry of Education
                </span>
            </div>

            <div class="max-w-md">
                <h2 class="text-4xl font-bold leading-tight tracking-tight">
                    Every textbook,<br>from press to <span class="text-gold-400">pupil</span>.
                </h2>
                <p class="mt-4 text-brand-100/90">
                    Plan print orders, move packages through regional, zone and woreda stores, and confirm
                    delivery at every school — all in one place.
                </p>

                <ol class="mt-10 space-y-3 text-sm">
                    @foreach (['Print & package', 'Store & route', 'Distribute to schools', 'Trace every copy'] as $step)
                    <li class="flex items-center gap-3">
                        <span class="flex items-center justify-center text-xs font-bold rounded-full w-7 h-7 bg-white/10 ring-1 ring-white/20 text-gold-300">{{ $loop->iteration }}</span>
                        <span class="text-brand-50">{{ $step }}</span>
                    </li>
                    @endforeach
                </ol>
            </div>

            <div class="brand-stripe max-w-[10rem] rounded-full"></div>
        </div>
    </div>

    {{-- Form panel --}}
    <div class="flex flex-col items-center justify-center flex-1 px-6 py-12 bg-slate-50 sm:px-12">
        <div class="w-full max-w-sm">
            <div class="mb-10">
                {{ $logo }}
            </div>
            {{ $slot }}
        </div>
    </div>
</div>
