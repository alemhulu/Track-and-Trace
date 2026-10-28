<x-guest-layout>
    <x-jet-authentication-card>
        <x-slot name="logo">
            <x-jet-authentication-card-logo />
        </x-slot>

        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Sign in</h1>
        <p class="mt-1 mb-8 text-sm text-slate-500">Use your ministry account to continue.</p>

        <x-jet-validation-errors class="mb-4" />

        @if (session('status'))
            <div class="px-4 py-3 mb-4 text-sm font-medium border rounded-lg text-emerald-800 bg-emerald-50 border-emerald-200">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf

            <div>
                <x-jet-label for="email" value="{{ __('Email') }}" />
                <x-jet-input id="email" class="py-2.5" type="email" name="email" :value="old('email')"
                    placeholder="name@moe.gov.et" required autofocus autocomplete="username" />
            </div>

            <div>
                <div class="flex items-center justify-between">
                    <x-jet-label for="password" value="{{ __('Password') }}" />
                    @if (Route::has('password.request'))
                        <a class="text-sm font-medium text-brand-600 hover:text-brand-800" href="{{ route('password.request') }}">
                            {{ __('Forgot password?') }}
                        </a>
                    @endif
                </div>
                <x-jet-input id="password" class="py-2.5" type="password" name="password" required
                    autocomplete="current-password" />
            </div>

            <label for="remember_me" class="flex items-center gap-2">
                <x-jet-checkbox id="remember_me" name="remember" />
                <span class="text-sm text-slate-600">{{ __('Keep me signed in') }}</span>
            </label>

            <x-jet-button class="w-full py-2.5">
                {{ __('Sign in') }}
            </x-jet-button>
        </form>

        <p class="mt-10 text-xs text-center text-slate-400">© {{ date('Y') }} Ministry of Education · Ethiopia</p>
    </x-jet-authentication-card>
</x-guest-layout>
