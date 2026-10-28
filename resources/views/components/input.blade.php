@props(['disabled' => false])
@php
$name = $attributes->get('name');
$value = old($name) ?? '';
@endphp
<input {{ $disabled ? 'disabled' : '' }} @error($name) {!! $attributes->merge(['class' => 'form-control form-control-error']) !!}
@enderror
{!! $attributes->merge(['class' => 'form-control', 'value' => $value]) !!}>
