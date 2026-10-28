@if (session('success'))
<div x-data="{ showAlert: true }" x-cloak x-show="showAlert" x-transition.opacity
    x-init="setTimeout(() => showAlert = false, 5000)"
    class="relative flex items-start gap-3 px-4 py-3 overflow-hidden text-sm border rounded-xl bg-emerald-50 border-emerald-200 text-emerald-800 dark:bg-emerald-950/40 dark:border-emerald-900 dark:text-emerald-200" role="alert">
    <i class="flex mt-0.5 text-lg fi fi-rr-shield-check"></i>
    <p class="flex-1 font-medium">{{ session('success') }}</p>
    <button type="button" class="opacity-60 hover:opacity-100" @click="showAlert = false" aria-label="Dismiss">
        <i class="flex fi fi-rr-cross-small"></i>
    </button>
    <span class="absolute inset-x-0 bottom-0 h-0.5 bg-emerald-500 animate-life"></span>
</div>
@endif

@if (session('error'))
<div x-data="{ showAlert: true }" x-cloak x-show="showAlert" x-transition.opacity
    x-init="setTimeout(() => showAlert = false, 5000)"
    class="relative flex items-start gap-3 px-4 py-3 overflow-hidden text-sm border rounded-xl bg-rose-50 border-rose-200 text-rose-800 dark:bg-rose-950/40 dark:border-rose-900 dark:text-rose-200" role="alert">
    <i class="flex mt-0.5 text-lg fi fi-rr-shield-exclamation"></i>
    <p class="flex-1 font-medium">{{ session('error') }}</p>
    <button type="button" class="opacity-60 hover:opacity-100" @click="showAlert = false" aria-label="Dismiss">
        <i class="flex fi fi-rr-cross-small"></i>
    </button>
    <span class="absolute inset-x-0 bottom-0 h-0.5 bg-rose-500 animate-life"></span>
</div>
@endif

@if (session('warning'))
<div x-data="{ showAlert: true }" x-cloak x-show="showAlert" x-transition.opacity
    x-init="setTimeout(() => showAlert = false, 5000)"
    class="relative flex items-start gap-3 px-4 py-3 overflow-hidden text-sm border rounded-xl bg-amber-50 border-amber-200 text-amber-800 dark:bg-amber-950/40 dark:border-amber-900 dark:text-amber-200" role="alert">
    <i class="flex mt-0.5 text-lg fi fi-rr-exclamation"></i>
    <p class="flex-1 font-medium">{{ session('warning') }}</p>
    <button type="button" class="opacity-60 hover:opacity-100" @click="showAlert = false" aria-label="Dismiss">
        <i class="flex fi fi-rr-cross-small"></i>
    </button>
    <span class="absolute inset-x-0 bottom-0 h-0.5 bg-amber-500 animate-life"></span>
</div>
@endif
