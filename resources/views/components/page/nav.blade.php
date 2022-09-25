@props(['col'])
@php
$col = [
'2' => 'lg:grid-cols-2',
'3' => 'lg:grid-cols-3',
'4' => 'lg:grid-cols-4',
'5' => 'lg:grid-cols-5',
'6' => 'lg:grid-cols-6',
'7' => 'lg:grid-cols-7',
'8' => 'lg:grid-cols-8',
'9' => 'lg:grid-cols-9',
'10' => 'lg:grid-cols-10',
'11' => 'lg:grid-cols-11',
'12' => 'lg:grid-cols-12',
][$col ?? '4'];
@endphp
<ul class="mt-3 grid grid-cols gap-4 sm:gap-5 sm:grid-cols-2 {{ $col }}">
    {{$slot}}
</ul>