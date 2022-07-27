@props(['active', 'name', 'icon'])
@php
$classes = ($active ?? false)
? 'hover:bg-blue-500 bg-blue-600 text-white group flex items-center px-2 py-2 text-sm font-semibold rounded-md'
: 'text-gray-600 hover:bg-blue-600 hover:text-white group flex items-center px-2 py-2 text-sm font-semibold rounded-md';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }} >
    <i class="fi {{ $icon ?? "" }} mr-3 flex text-2xl z-10 py-1"></i>
    {{ $name ?? $slot }}
</a>