@props(['disabled' => false])
@php
$name = $attributes->whereStartsWith('wire:model')->first();
$value = old($name) ?? '';
@endphp
<input {{ $disabled ? 'disabled' : '' }} @error($name) {!! $attributes->merge(['class'=>'block w-full rounded-md
shadow-sm focus:border-indigo-500
focus:ring-1 border dark:focus:border-gray-300
border-red-300 bg-red-50/50']) !!}
@enderror {!! $attributes->merge(['class' => 'block w-full border-gray-300
focus:border-indigo-300 dark:focus:border-gray-300
rounded-md shadow-sm placeholder:text-gray-400 drak:bg-gray-500
placeholder:text-sm mt-1']) !!}>
<x-jet-input-error for="{{ $name }}" alert="{{ $name }}" />