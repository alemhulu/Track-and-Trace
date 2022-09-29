@props(['disabled' => false])
@php
$name = $attributes->get('name');
$value = old($name) ?? '';
@endphp
<input {{ $disabled ? 'disabled' : '' }} @error($name) {!! $attributes->merge(['class'=>'block w-full rounded-md
shadow-sm focus:border-indigo-500
focus:ring-1 border dark:focus:border-gray-300
border-red-300 bg-red-50/50']) !!}
@enderror
{!! $attributes->merge(['class' => 'block w-full rounded-md shadow-sm
focus:border-indigo-500 border-gray-300 focus:ring-1 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400
dark:text-gray-400 dark:focus:ring-blue-500 dark:focus:border-blue-500', 'value'=>$value]) !!}>