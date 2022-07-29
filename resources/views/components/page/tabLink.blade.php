@props(['name','link'])
<x-actionbtn onclick="vTab(event,'{{ $link }}')"
    class="flex items-center text-md  w-full font-semibold text-gray-600 active" aria-current="page">
    <span class="truncate px-4 py-1">
        {{ $name }}
    </span>
</x-actionbtn>