@aware(['entries'=>true])
<table {{ $attributes->merge(['class' => 'w-full'])}}>
    @if ($entries)
    <x-data-table.entries />
    @endif
    {{ $slot }}
    <div class="hidden md:grid-cols-5 md:grid-cols-6 md:grid-cols-7"></div>
</table>