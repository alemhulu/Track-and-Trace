@props([ 'preview'])
@php
$name = $attributes->whereStartsWith('wire:model')->first();
$value = old($name) ?? '';
@endphp
<div>
    <!-- Knowing is not enough; we must apply. Being willing is not enough; we must do. - Leonardo da Vinci -->
    <div class="flex items-center space-x-2">
        <span class=" shrink-0 h-16 w-16 rounded-full overflow-hidden bg-gray-100 object-cover">
            @if ($preview)
            <img class="h-16 w-16 object-cover rounded-full" src="{{ $preview->temporaryUrl() }}"
                alt="Current profile photo" />
            @endif
        </span>
        <input type="file" wire:model="{{ $name }}" class="block w-full text-sm text-slate-500 dark:text-slate-100 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0
                    file:text-sm file:font-semibold  file:bg-gray-50 file:text-gray-700 hover:file:bg-gray-100 cursor-pointer
                    " accept="image/*" />
    </div>
    <x-jet-input-error for="{{ $name }}" alert="{{ $name }}" />
</div>