@props(['link', 'component'=>""])
<div id="{{ $link }}" class="space-y-3 sm:px-6 lg:px-0 lg:col-span-9 vtab" style="display:none; ">
    @if ($component === "")
    add Component
    @else
    @livewire($component)
    @endif
</div>