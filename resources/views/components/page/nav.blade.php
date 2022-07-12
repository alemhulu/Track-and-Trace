@props(['col'])

@if ($col == 2)
<ul class="mt-3 grid grid-cols gap-4 sm:gap-5 sm:grid-cols-2 lg:grid-cols-2">
    {{$slot}}
</ul>
@endif
@if ($col == 3)
<ul class="mt-3 grid grid-cols gap-4 sm:gap-5 sm:grid-cols-2 lg:grid-cols-3">
    {{$slot}}
</ul>
@endif
@if ($col == 4)
<ul class="mt-3 grid grid-cols gap-4 sm:gap-5 sm:grid-cols-2 lg:grid-cols-4">
    {{$slot}}
</ul>
@endif
@if ($col == 5)
<ul class="mt-3 grid grid-cols gap-4 sm:gap-5 sm:grid-cols-2 lg:grid-cols-5">
    {{$slot}}
</ul>
@endif