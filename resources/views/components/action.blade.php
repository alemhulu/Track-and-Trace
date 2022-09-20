@props(['id', 'view', 'edit', 'delete'])

@php
$info = "flex justify-center items-center px-1 p-1 transition
delay-75 ease-out transform hover:-translate-y-0.5 hover:scale-105 gap-2 border rounded-md
border border-blue-600 dark:border-blue-300 hover:bg-blue-500 dark:hover-blue-700 mx-1 border-opacity-30
hover:text-white
active:bg-blue-600 active:hover:scale-110 focus:scale-105 focus:-translate-y-0.5 focus:text-lime-900
focus:bg-blue-400";

$warning = "flex justify-center items-center px-1 p-1 transition
delay-75 ease-out transform hover:-translate-y-0.5 hover:scale-105 gap-2 border rounded-md
border border-yellow-400 dark:border-yellow-300 hover:bg-yellow-500 dark: hover:bg-yellow-600 mx-1 border-opacity-30
hover:text-white
active:bg-yellow-600 active:hover:scale-110 focus:scale-105 focus:-translate-y-0.5 focus:text-lime-900
focus:bg-yellow-400 dark:text-gray-200";

$danger = "flex justify-center items-center px-1 p-1 transition
delay-75 ease-out transform hover:-translate-y-0.5 hover:scale-105 gap-2 border rounded-md
border border-red-400 dark:border-red-300 hover:bg-red-500 dark:hover:bg-red-600 mx-1 border-opacity-30 hover:text-white
active:bg-red-600 active:hover:scale-110 focus:scale-105 focus:-translate-y-0.5 focus:text-lime-900
focus:bg-red-400 dark:text-gray-200";

$id = " ";
@endphp

<div class="flex  items-center text-xs">
    @isset($view)
    <a href="{{ $view }}" wire:click="{{ $view }}({{$id}})">
        <div class="{{$info }}">
            <i class="fi fi-rr-eye flex"></i>
        </div>
    </a>
    @endisset

    @isset($edit)
    <a href="{{ $edit }}" wire:click="{{ $edit }}({{$id}})">
        <div class="{{ $warning }}">
            <i class="fi fi-rr-edit flex"></i>
        </div>
    </a>
    @endisset

    @isset($delete)
    <form method="post" action="{{ $delete }}" class="{{ $danger }}">
        @method('delete')
        @csrf
        <button type="submit" class="">
            <div>
                <i class=" fi fi-rr-trash flex"></i>
            </div>
        </button>
    </form>
    @endisset
</div>