@if ($errors->any())
<div x-data="{ showAlert: true }" x-cloak x-show="showAlert" x-transition.opacity
    class="relative flex items-start gap-3 px-4 py-3 border rounded-xl bg-rose-50 border-rose-200 text-rose-800 dark:bg-rose-950/40 dark:border-rose-900 dark:text-rose-200"
    role="alert">
    <i class="flex mt-0.5 text-lg fi fi-rr-shield-exclamation"></i>
    <div class="flex-1">
        <p class="text-sm font-semibold">{{ __('Please fix the following:') }}</p>
        <ul class="mt-1.5 text-sm list-disc list-inside space-y-0.5">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    <button type="button" class="opacity-60 hover:opacity-100" @click="showAlert = false" aria-label="Close">
        <i class="flex fi fi-rr-cross-small"></i>
    </button>
</div>
@endif
