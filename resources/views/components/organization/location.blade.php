@props(['country'=>'', 'region'=>'', 'zone'=>'' ])
<div class="flex items-center">
    <div class="text-2xl text-blue-700 dark:text-gray-300 font-semibold tracking-tighter">
        <div class=""><i class="flex fi fi-rr-marker rounded-full p-2 items-center bg-blue-100"></i>
        </div>
    </div>
    <div class="ml-2 font-semibold text-gray-600 dark:text-gray-300">
        <div class="font-bold"> {{ $country }} </div>
        <div class="text-sm"> {{ $region }} </div>
        <div class="text-xs"> {{ $zone }} </div>
    </div>
</div>