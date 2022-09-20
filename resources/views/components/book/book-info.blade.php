@props(['image', 'grade', 'subject', 'type', 'edition', 'ISBN'])
<div class="grid grid-cols-2 items-center">
    <div class="flex flex-col h-40 w-24 bg-cover bg-center rounded" style="background-image: url('{{ $image }}')">
    </div>
    <div class="flex flex-col">
        <div class="text-md font-semibold text-gray-600 dark:text-gray-200">
            {{ $grade }}
        </div>
        <div class="text-md font-semibold text-gray-600 dark:text-gray-200">
            {{ $subject }}
        </div>
        <div class="text-sm font-semibold text-gray-500 dark:text-gray-300">
            {{ $type }}
        </div>
        <div class="text-sm font-semibold text-gray-500  dark:text-gray-300 ordinal">
            {{ $edition }}
        </div>
        <div class="text-sm font-semibold text-gray-500 dark:text-gray-300  tabular-nums">
            ISBN: {{ $ISBN }}
        </div>
    </div>
</div>