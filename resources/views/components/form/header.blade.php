@props(['search' => false])
<div class="card-header">
    <h2 class="card-title">
        {{ $slot }}
    </h2>

    <div class="{{ $search ? '' : 'hidden' }} w-full sm:max-w-xs">
        <label for="search" class="sr-only">Search</label>
        <div class="relative">
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                <i class="flex fi fi-rr-search"></i>
            </div>
            <input id="search" name="search" wire:model.debounce.500ms="search" class="form-control pl-9"
                placeholder="Search…" type="search">
        </div>
    </div>
</div>
