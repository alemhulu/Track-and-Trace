@props(['country'=>'', 'region'=>'', 'zone'=>'' ])
<div class="flex items-center">
    <div class="text-sm text-gray-500 dark:text-gray-300 font-semibold tracking-tighter">
        <div class="">Country:</div>
        <div class="">Region / City:</div>
        <div class="">Zone / Sub-city:</div>
    </div>
    <div class="ml-4 text-sm font-semibold text-gray-600 dark:text-gray-300">
        <div class=""> {{ $country }} </div>
        <div class=""> {{ $region }} </div>
        <div class=""> {{ $zone }} </div>
    </div>
</div>