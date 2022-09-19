@aware(['entries'=>true])
<table {{ $attributes->merge(['class' => 'w-full'])}}>
    @if ($entries)
    <x-data-table.entries />
    @endif
    {{ $slot }}
</table>