@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'block w-full border-gray-300
focus:border-indigo-300 dark:focus:border-gray-300
rounded-md shadow-sm placeholder:text-gray-400 drak:bg-gray-500
placeholder:text-sm mt-1']) !!}>