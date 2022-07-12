<tr {{ $attributes->merge(
    ['class' => 'hover:bg-lime-100 hover:border-dashed hover:border hover:border-lime-300']
    ) }}>
    {{ $slot }}
</tr>