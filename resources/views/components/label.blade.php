@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-medium text-sm text-gray-700 dark:text-gray-200 mb-1']) }}>
    {{ $value ?? $slot }}
</label>