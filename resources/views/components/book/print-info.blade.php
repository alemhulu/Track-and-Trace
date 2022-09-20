@props(['batch', 'copies', 'packages'])
<div class="flex items-center">
    <div class="text-sm text-gray-500 dark:text-gray-300 font-semibold text-left">
        <div class="">
            Batch No.:
        </div>
        <div class="">
            No. Copies:
        </div>
        <div class="">
            No. Packages:
        </div>
    </div>
    <div class="ml-3 text-sm font-medium text-gray-700 dark:text-gray-100">
        <div class="">
            {{ $batch }}
        </div>
        <div class="">
            {{ $copies }}
        </div>
        <div class="">
            {{ $packages }}
        </div>
    </div>
</div>