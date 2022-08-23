@if ($errors->any())
<div x-data="{ showAlert: true }">
    <div x-cloak x-show="showAlert" x-init="setTimeout(() => showAlert = false, 5000)"
        class="relative mb-4 bg-red-50 rounded-lg px-5 py-3 shadow-sm">
        <button class="absolute top-2 right-3 z-20" @click="showAlert = false" aria-label="Close">
            <i class="fi fi-rr-cross text-red-700"></i>
        </button>

        <div class=" font-semibold text-red-700">
            {{ __('Whoops! Something went wrong.') }}
        </div>

        <ul class="mt-3 list-disc list-inside text-sm text-red-700">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
        <span class="absolute inset-x-0  top-0 h-1.5 bg-red-500 animate-life rounded-t-lg"></span>
    </div>
</div>
@endif