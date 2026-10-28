@php
$name = $attributes->whereStartsWith('wire:model')->first();
$value = old($name) ?? '';
@endphp
<select @error($name) {!! $attributes->merge(['class' => 'form-control form-control-error mt-1 pr-10']) !!}
    @enderror
    {{ $attributes->merge(['class' => 'form-control mt-1 pr-10']) }}>
    {{ $slot }}
</select>
<x-jet-input-error for="{{ $name }}" alert="{{ $name }}" />
