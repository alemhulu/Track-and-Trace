<tr {{ $attributes->merge(
    ['class' => 'hover:bg-lime-50 hover:border-dashed hover:border hover:border-lime-300']
    ) }}>
    {{ $slot }}
</tr>