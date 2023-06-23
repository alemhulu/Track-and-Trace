@props(['image' =>'', 'name'=>'', 'email'=>'', 'phone'=>'', 'h'=>12])
@php
if ($image != "") {
if(substr($image,0, 4) != 'http' && substr($image,0, 4) != 'data' && substr($image,0, 1) != '/'){
$image = '/'.$image;
}
}
@endphp
<div class="flex items-center">
    @if ($image)
    <div class="flex-shrink-0 h-{{ $h }} w-{{ $h }} mr-2 ">
        <img class="flex h-{{ $h }} w-{{ $h }} rounded-full object-cover" src="{!! $image !!}" alt="">
    </div>
    @endif

    <div class="">
        <div class=" text-sm font-semibold text-gray-600 dark:text-gray-200">
            <div class="font-bold"> {!! $name !!} </div>
            <div class="text-xs"> {{ $email }} </div>
            <div class="text-xs"> {{ $phone }} </div>
        </div>
    </div>
</div>