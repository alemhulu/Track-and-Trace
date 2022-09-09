@aware(['entries'=>true])
<table {{ $attributes->merge(['class' => 'min-w-full '])}}>
    @if ($entries)
    <x-data-table.entries />
    @endif
    {{ $slot }}
</table>