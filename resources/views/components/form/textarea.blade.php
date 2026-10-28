@php
$name = $attributes->whereStartsWith('wire:model')->first();
$value = old($name) ?? '';
@endphp
<textarea @error($name) {{ $attributes->merge(['class' => 'form-control form-control-error mt-1'])  }}
@enderror
{{ 
  $attributes->merge(['class' => 'form-control mt-1']) }}>

{{ $value }} {{ $slot }}

  </textarea>
{{-- <p class="mt-2 text-sm text-gray-500">Write a few sentences about yourself.</p> --}}