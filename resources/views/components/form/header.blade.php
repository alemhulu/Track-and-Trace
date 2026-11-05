@props(['search' => false, 'collapsible' => false, 'subtitle' => null])
<div class="card-header">
    <div class="flex items-center min-w-0 gap-2">
        @if ($collapsible)
        <button type="button" x-on:click="open = ! open" class="icon-btn" :aria-expanded="open.toString()"
            :title="open ? 'Collapse' : 'Expand'" aria-label="Toggle section">
            <i class="flex transition-transform fi fi-rr-angle-small-down" :class="open ? '' : '-rotate-90'"></i>
        </button>
        @endif
        <div class="min-w-0">
            <h2 class="card-title" @if ($collapsible) x-on:click="open = ! open" role="button" @endif>
                {{ $slot }}
            </h2>
            @if ($subtitle)
            <p class="card-subtitle">{{ $subtitle }}</p>
            @endif
        </div>
    </div>

    <div class="{{ $search ? '' : 'hidden' }} w-full sm:max-w-xs" @if ($collapsible) x-show="open" @endif>
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
