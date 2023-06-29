@aware(['id' => '', 'view', 'edit', 'delete', 'add', 'text'=>'Add' , 'link'=>false])

@php
$btn = "flex justify-center items-center px-2 py-1.5 transition
delay-75 ease-out transform hover:-translate-y-0.5 hover:scale-105 gap-1 border rounded-full
border border-blue-600 dark:border-blue-300 hover:bg-blue-500 dark:hover-blue-600 mx-2 border-opacity-40
hover:text-white
active:bg-blue-600 active:hover:scale-110 focus:scale-105 focus:-translate-y-0.5 focus:text-lime-900
focus:bg-blue-400 font-bold text-gray-500 dark:text-gray-300 cursor-pointer";

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
focus:bg-yellow-400";

$danger = "flex justify-center items-center px-1 p-1 transition
delay-75 ease-out transform hover:-translate-y-0.5 hover:scale-105 gap-2 border rounded-md
border border-red-400 dark:border-red-300 hover:bg-red-500 dark:hover:bg-red-600 mx-1 border-opacity-30 hover:text-white
active:bg-red-600 active:hover:scale-110 focus:scale-105 focus:-translate-y-0.5 focus:text-lime-900
focus:bg-red-400";
@endphp

<div class="flex  items-center text-xs">
    @if ($link)
    {{-- @isset($add)
    <a wire:click="{{ $add }}()">
    <div class="{{$btn }}">
        <i class="fi fi-rr-plus flex dark:text-gray-300"></i>
        {{ $text }}
    </div>
    </a>
    @endisset --}}

    @isset($view)
    <a href="{{ $view }}">
        <div class="{{$info }}">
            <i class="fi fi-rr-eye flex dark:text-gray-300"></i>
        </div>
    </a>
    @endisset

    {{-- @isset($edit)
    <a href="#{{ $edit }}" wire:click="{{ $edit }}({{$id}})">
    <div class="{{ $warning }}">
        <i class="fi fi-rr-edit flex dark:text-gray-300"></i>
    </div>
    </a>
    @endisset --}}

    {{-- @isset($delete)
    <a href="#{{ $delete }}" x-on:Click="deleteId = {{ $id }}" wire:click="deleteId({{ $id }})">
    <div class="{{ $danger }}"">
            <i class=" fi fi-rr-trash flex dark:text-gray-300"></i>
    </div>
    </a>
    @endisset --}}
    @else
    @isset($add)
    <a wire:click="{{ $add }}()">
        <div class="{{$btn }}">
            <i class="fi fi-rr-plus flex dark:text-gray-300"></i>
            {{ $text }}
        </div>
    </a>
    @endisset

    @isset($view)
    <a wire:click="{{ $view }}({{$id}})">
        <div class="{{$info }}">
            <i class="fi fi-rr-eye flex dark:text-gray-300"></i>
        </div>
    </a>
    @endisset

    @isset($edit)
    <a href="#{{ $edit }}" wire:click="{{ $edit }}({{$id}})">
        <div class="{{ $warning }}">
            <i class="fi fi-rr-edit flex dark:text-gray-300"></i>
        </div>
    </a>
    @endisset

    @isset($delete)
    <a href="#{{ $delete }}" x-on:Click="deleteId = {{ $id }}" wire:click="deleteId({{ $id }})">
        <div class="{{ $danger }}"">
            <i class=" fi fi-rr-trash flex dark:text-gray-300"></i>
        </div>
    </a>
    @endisset
    @endif

</div>