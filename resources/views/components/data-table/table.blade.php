<table {{ $attributes->merge(['class' => 'min-w-full '])}}>
    <x-data-table.entries />
    {{ $slot }}
</table>