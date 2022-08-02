@props(['name', 'type', 'size'])
<div class="flex items-center">
    <div class="text-sm font-semibold text-left text-gray-500">
        <div class="">
            File Name:
        </div>
        <div class="">
            File Type:
        </div>
        <div class="">
            File Size:
        </div>
    </div>
    <div class="ml-3 text-sm font-medium text-gray-700">
        <div class="">
            {{ $name }}
        </div>
        <div class="">
            {{ $type }}
        </div>
        <div class="">
            {{$size}}
        </div>
    </div>
</div>