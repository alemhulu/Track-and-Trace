@php
$name = $attributes->whereStartsWith('wire:model')->first();
$value = old($name) ?? '';
@endphp
<select @error($name) {!! $attributes->merge(['class'=>'block w-full appearance-none border-red-300
    focus:border-indigo-300
    focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm placeholder:text-gray-400
    placeholder:text-xs mt-1 bg-red-50/50']) !!}
    @enderror
    {{ $attributes->merge([ 'class' => 'block w-full appearance-none border-gray-300  rounded-md shadow-sm placeholder:text-gray-400
    placeholder:text-xs mt-1 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500']) }}>
    {{ $slot }}
</select>
<x-jet-input-error for="{{ $name }}" alert="{{ $name }}" />