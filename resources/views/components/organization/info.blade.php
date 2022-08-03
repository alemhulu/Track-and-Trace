@props(['image' =>'', 'name'=>'', 'email'=>'', 'phone'=>''])
<div class="flex items-center">
    <div class="flex-shrink-0 h-16 w-16">
        <img class="h-16 w-16 rounded-lg " src="{{ $image }}" alt="">
    </div>
    <div class="ml-2">
        <div class="ml-1 text-sm font-semibold text-gray-600">
            <div class=""> {{ $name }} </div>
            <div class=""> {{ $email }} </div>
            <div class=""> {{ $phone }} </div>
        </div>
    </div>
</div>