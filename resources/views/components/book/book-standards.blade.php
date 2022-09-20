@props(['font', 'print', 'paper'])
<div class="flex items-center">
    <div class="text-sm text-gray-500 dark:text-gray-300 font-semibold text-left">
        <div class="">
            Font Type:
        </div>
        <div class="">
            Print Type:
        </div>
        <div class="">
            Paper Size:
        </div>
    </div>
    <div class="ml-3 text-sm font-medium text-gray-700 dark:text-gray-100">
        <div class="">
            {{ $font }}
        </div>
        <div class="">
            {{ $print }}
        </div>
        <div class="">
            {{ $paper }}
        </div>
    </div>
</div>