@props(['name'=>'', 'email'=>'', 'phone'=>'' ])
<div class="flex items-center">
    <div class="text-xs text-gray-500 dark:text-gray-300 font-semibold tracking-tighter">
        <div class="">Name:</div>
        <div class="">Email:</div>
        <div class="">Phone:</div>
    </div>
    <div class="ml-4 text-xs font-semibold text-gray-600 dark:text-gray-200">
        <div class="font-bold"> {{ $name }} </div>
        <div class=""> {{ $email }} </div>
        <div class=""> {{ $phone }} </div>
    </div>
</div>