@props(['name','value','model'])

@isset($value)
<div class="col-span-2 bg-lime-50 rounded p-1 border border-lime-200 hover:bg-lime-100">
    <label for="{{ $name }}" class="flex items-center cursor-pointer">
        <x-jet-checkbox wire:model='{{ $model }}' value="{{ $value}}" id="{{ $name }}" name="{{ $name }}"
            class="w-5 h-5" />
        <span class="ml-2 text-sm text-gray-600 truncate">{{ $name }}</span>
    </label>
</div>
@else
<div class="col-span-2 bg-lime-50 rounded p-1 border border-lime-200 hover:bg-lime-100">
    <label for="{{ $name }}" class="flex items-center cursor-pointer">
        <x-jet-checkbox id="{{ $name }}" name="{{ $name }}" class="w-5 h-5" />
        <span class="ml-2 text-sm text-gray-600 truncate">{{ $name }}</span>
    </label>
</div>
@endisset