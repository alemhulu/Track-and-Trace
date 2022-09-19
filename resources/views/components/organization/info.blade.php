@props(['image' =>'', 'name'=>'', 'email'=>'', 'phone'=>'', 'h'=>16])
<div class="flex items-center">
    @if ($image)
    <div class="flex-shrink-0 h-{{ $h }} w-{{ $h }} mr-2">
        <img class="h-{{ $h }} w-{{ $h }} rounded-lg " src="/{{ $image }}" alt="">
    </div>
    @endif

    <div class="">
        <div class=" text-sm font-semibold text-gray-600">
            <div class=""> {{ $name }} </div>
            <div class=""> {{ $email }} </div>
            <div class=""> {{ $phone }} </div>
        </div>
    </div>
</div>