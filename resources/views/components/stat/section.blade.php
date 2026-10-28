@props(['name' => 'subject', 'col'])
@php
$col = [
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
<div {{ $attributes->merge([ 'class' => '']) }}>
    <section>
        <h3 class="mb-3 text-lg font-semibold capitalize text-slate-900 dark:text-white">
            {{ $name }}
        </h3>
        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2 {{ $col }}">
            {{ $slot }}
        </dl>
    </section>
</div>
