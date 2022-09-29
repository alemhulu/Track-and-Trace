@props(['disabled' => false])
@php
$name = $attributes->whereStartsWith('wire:model')->first();
$value = old($name) ?? '';
@endphp
<input {{ $disabled ? 'disabled' : '' }} @error($name) {!! $attributes->merge(['class'=>'block w-full rounded-md
shadow-sm focus:border-indigo-500
focus:ring-1 border dark:focus:border-red-300
border-red-300 bg-red-50/50']) !!}
@enderror {!! $attributes->merge(['class' => 'block w-full border-gray-300
focus:border-indigo-300
rounded-md shadow-sm placeholder:text-gray-400
placeholder:text-sm mt-1 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-gray-400
dark:focus:ring-blue-500 dark:focus:border-blue-500']) !!}>
<x-jet-input-error for="{{ $name }}" alert="{{ $name }}" />