@props(['name'=>'', 'email'=>'', 'phone'=>'' ])
<div class="flex items-center">
    <div class="text-sm text-gray-500 font-semibold tracking-tighter">
        <div class="">Name:</div>
        <div class="">Email:</div>
        <div class="">Phone:</div>
    </div>
    <div class="ml-4 text-sm font-semibold text-gray-600">
        <div class=""> {{ $name }} </div>
        <div class=""> {{ $email }} </div>
        <div class=""> {{ $phone }} </div>
    </div>
</div>