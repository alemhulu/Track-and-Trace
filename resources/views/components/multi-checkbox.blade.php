@props(['title', 'name', 'value', 'checked'=>false])

@isset($value)
<div class="lg:col-span-1 sm:col-span-2 bg-lime-50 rounded p-1 border border-lime-200 hover:bg-lime-100 mt-2 sm:mt-0">
    <label for="{{ $title }}" class="flex items-center cursor-pointer">
        @if ($checked)
        <x-checkbox value="{{ $value }}" id="{{ $title }}" name="{{ $name }}" class="w-5 h-5" checked />
        @else
        <x-checkbox value="{{ $value }}" id="{{ $title }}" name="{{ $name }}" class="w-5 h-5" />
        @endif
        <span class="ml-2 text-sm text-gray-600 truncate">{{ $title }}</span>
    </label>
</div>
@else
<div class="lg:col-span-1  sm:col-span-2 bg-lime-50 rounded p-1 border border-lime-200 hover:bg-lime-100 mt-2 sm:mt-0">
    <label for="{{ $name }}" class="flex items-center cursor-pointer">
        <x-checkbox id="{{ $name }}" name="{{ $name }}" class="w-5 h-5" />
        <span class="ml-2 text-sm text-gray-600 truncate">{{ $title }}</span>
    </label>
</div>
@endisset