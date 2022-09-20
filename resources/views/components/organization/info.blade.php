@props(['image' =>'', 'name'=>'', 'email'=>'', 'phone'=>'', 'h'=>16])
@php
if ($image != "") {
if(substr($image,0, 4) != 'http' && substr($image,0, 4) != 'data'){
$image = '/'.$image;
}
}
@endphp
<div class="flex items-center">
    @if ($image)
    <div class="flex-shrink-0 h-{{ $h }} w-{{ $h }} mr-2">
        <img class="h-{{ $h }} w-{{ $h }} rounded-lg " src="{{ $image }}" alt="">
    </div>
    @endif

    <div class="">
        <div class=" text-sm font-semibold text-gray-600 dark:text-gray-200">
            <div class=""> {{ $name }} </div>
            <div class=""> {{ $email }} </div>
            <div class=""> {{ $phone }} </div>
        </div>
    </div>
</div>