@aware(['id' => '', 'view', 'edit', 'delete', 'add', 'text'=>'Add' , 'link'=>false])

@php
$btn = "icon-btn-add";

$info = "icon-btn-view";

$warning = "icon-btn-edit";

$danger = "icon-btn-delete";
@endphp

<div class="flex items-center gap-1.5 text-xs">
    @if ($link)
    @isset($add)
    <a wire:click="{{ $add }}()">
    <div class="{{$btn }}">
        <i class="flex fi fi-rr-plus"></i>
        {{ $text }}
    </div>
    </a>
    @endisset

    @isset($view)
    <a href="{{ $view }}">
        <div class="{{$info }}">
            <i class="flex fi fi-rr-eye"></i>
        </div>
    </a>
    @endisset

    @isset($edit)
    <a href="#{{ $edit }}" wire:click="{{ $edit }}({{$id}})">
    <div class="{{ $warning }}">
        <i class="flex fi fi-rr-edit"></i>
    </div>
    </a>
    @endisset

    @isset($delete)
    <a href="#{{ $delete }}" x-on:Click="deleteId = {{ $id }}" wire:click="deleteId({{ $id }})">
    <div class="{{ $danger }}">
            <i class="flex  fi fi-rr-trash"></i>
    </div>
    </a>
    @endisset
    @else
    @isset($add)
    <a wire:click="{{ $add }}()">
        <div class="{{$btn }}">
            <i class="flex fi fi-rr-plus"></i>
            {{ $text }}
        </div>
    </a>
    @endisset

    @isset($view)
    <a wire:click="{{ $view }}({{$id}})">
        <div class="{{$info }}">
            <i class="flex fi fi-rr-eye"></i>
        </div>
    </a>
    @endisset

    @isset($edit)
    <a href="#{{ $edit }}" wire:click="{{ $edit }}({{$id}})">
        <div class="{{ $warning }}">
            <i class="flex fi fi-rr-edit"></i>
        </div>
    </a>
    @endisset

    @isset($delete)
        <a href="#{{ $delete }}" x-on:Click="deleteId = {{ $id }}" wire:click="{{ $delete }}({{ $id }})">
        <div class="{{ $danger }}">
            <i class="flex  fi fi-rr-trash"></i>
        </div>
    </a>
    @endisset
    @endif

</div>
