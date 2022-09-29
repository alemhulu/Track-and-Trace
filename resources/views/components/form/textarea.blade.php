@php
$name = $attributes->whereStartsWith('wire:model')->first();
$value = old($name) ?? '';
@endphp
<textarea @error($name) {{ $attributes->merge(['class' =>'shadow-sm block w-full focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm border border-red-300 rounded-md bg-red-50/50'])  }}
@enderror
{{ 
  $attributes->merge(['class' => 'shadow-sm block w-full border-gray-300
focus:border-indigo-300
focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm placeholder:text-gray-400
placeholder:text-sm mt-1 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500']) }}>

{{ $value }} {{ $slot }}

  </textarea>
{{-- <p class="mt-2 text-sm text-gray-500">Write a few sentences about yourself.</p> --}}