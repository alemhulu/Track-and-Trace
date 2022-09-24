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
    <section class="bg-white dark:bg-gray-800 rounded-lg">
        <div class="px-4 py-5 mx-auto sm:px-6 lg:px-8 mb-3">
            <p class="text-xl font-bold text-gray-800 sm:text-2xl dark:text-gray-400 capitalize">
                {{ $name }}
            </p>
            <div class="my-4">
                <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2 {{ $col }}">
                    {{ $slot }}
                </dl>
            </div>
        </div>
    </section>
</div>