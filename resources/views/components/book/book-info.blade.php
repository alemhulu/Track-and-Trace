@props(['image', 'grade', 'subject', 'type', 'edition', 'ISBN'])
<div class="flex items-center">
    <div class="flex-shrink-0 h-40 w-24 bg-cover bg-center rounded" style="background-image: url('{{ $image }}')">
    </div>
    <div class="ml-4">
        <div class="text-md font-semibold text-gray-600">
            {{ $grade }}
        </div>
        <div class="text-md font-semibold text-gray-600">
            {{ $subject }}
        </div>
        <div class="text-sm font-semibold text-gray-500">
            {{ $type }}
        </div>
        <div class="text-sm font-semibold text-gray-500 ordinal">
            {{ $edition }}
        </div>
        <div class="text-sm font-semibold text-gray-500 tabular-nums">
            ISBN: {{ $ISBN }}
        </div>
    </div>
</div>