@props(['disabled' => false])
@php
$name = $attributes->get('name');
$value = old($name) ?? '';
@endphp
<input {{ $disabled ? 'disabled' : '' }} @error($name) {!! $attributes->merge(['class'=>'block w-full rounded-md
shadow-sm focus:ring-indigo-500 focus:border-indigo-500
focus:ring-1 border
border-red-300 bg-red-50/50']) !!}
@enderror
{!! $attributes->merge(['class' => 'block w-full rounded-md shadow-sm
focus:ring-indigo-500 focus:border-indigo-500 border-gray-300 focus:ring-1', 'value'=>$value]) !!}>