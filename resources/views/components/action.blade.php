@props(['id', 'view', 'edit', 'delete'])

@php
$info = "icon-btn-view";

$warning = "icon-btn-edit";

$danger = "icon-btn-delete";

$id = " ";
@endphp

<div class="flex items-center gap-1.5 text-xs">
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