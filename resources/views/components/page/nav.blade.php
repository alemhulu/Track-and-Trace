@props(['col' => null])
{{-- Module sub-navigation: a scrollable tab strip. `col` is kept for older callers. --}}
<nav class="p-1.5 card" aria-label="Section">
    <ul class="flex gap-1 overflow-x-auto no-scrollbar">
        {{ $slot }}
    </ul>
</nav>
