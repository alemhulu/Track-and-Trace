@if (session('success'))
<div x-data="{ showAlert: true }">
    <div x-cloak x-show="showAlert" x-init="setTimeout(() => showAlert = false, 5000)"
        class="relative  font-semibold text-sm text-green-700 bg-green-100 rounded-lg px-5 pt-3 pb-2 shadow-sm shadow-green-200"
        role="alert">
        <span class="absolute inset-x-0 top-0 h-1.5 bg-green-500 animate-life rounded-t-lg"></span>
        {{ session('success') }}
    </div>
</div>
@endif

@if (session('error'))
<div x-data="{ showAlert: true }">
    <div x-cloak x-show="showAlert" x-init="setTimeout(() => showAlert = false, 5000)"
        class="relative  font-semibold text-sm text-red-700 bg-red-100 rounded-lg px-5 pt-3 pb-2 shadow-sm shadow-red-200"
        role="alert">
        <span class="absolute inset-x-0 top-0 h-1.5 bg-red-500 animate-life rounded-t-lg"></span>
        {{ session('error') }}
    </div>
</div>
@endif

@if (session('warning'))
<div x-data="{ showAlert: true }">
    <div x-cloak x-show="showAlert" x-init="setTimeout(() => showAlert = false, 5000)"
        class="relative  font-semibold text-sm text-yellow-700 bg-yellow-100 rounded-lg px-5 pt-3 pb-2 shadow-sm shadow-yellow-200"
        role="alert">
        <span class="absolute inset-x-0 top-0 h-1.5 bg-yellow-500 animate-life rounded-t-lg"></span>
        {{ session('warning') }}
    </div>
</div>
@endif