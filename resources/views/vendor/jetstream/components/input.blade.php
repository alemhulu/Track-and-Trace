@props(['disabled' => false])
@php
$name = $attributes->whereStartsWith('wire:model')->first();
$value = old($name) ?? '';
@endphp
<input {{ $disabled ? 'disabled' : '' }} @error($name) {!! $attributes->merge(['class' => 'form-control form-control-error mt-1']) !!}
@enderror {!! $attributes->merge(['class' => 'form-control mt-1']) !!}>
<x-jet-input-error for="{{ $name }}" alert="{{ $name }}" />
