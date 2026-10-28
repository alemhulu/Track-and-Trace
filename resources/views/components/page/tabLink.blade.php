@props(['name','link'])
<x-actionbtn onclick="vTab(event,'{{ $link }}')"
    class="flex items-center w-full !justify-start text-sm font-medium text-slate-600 dark:text-slate-300 active" aria-current="page">
    <span class="truncate px-3 py-1">
        {{ $name }}
    </span>
</x-actionbtn>
