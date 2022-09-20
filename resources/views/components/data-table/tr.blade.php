<tr {{ $attributes->merge(
    ['class' => 'hover:bg-lime-50 dark:hover:bg-gray-700 hover:border-dashed hover:border hover:border-lime-300
    dark:hover:border-gray-600']
    ) }}>
    {{ $slot }}
</tr>