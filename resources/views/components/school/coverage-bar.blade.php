@props(['value', 'hint' => null])
{{-- A share shown as a bar: by default, required student books already in students' hands --}}
@if ($value === null)
<span class="text-gray-400">—</span>
@else
@php
    $percent = (int) round($value * 100);
    $color = $percent >= 95 ? 'bg-emerald-500' : ($percent >= 60 ? 'bg-amber-500' : 'bg-rose-500');
@endphp
<div class="flex items-center gap-2 min-w-[8rem]" title="{{ $hint ?? $percent . '% of required books are with students' }}">
    <div class="w-24 h-2 overflow-hidden bg-slate-100 rounded-full dark:bg-slate-800">
        <div class="h-2 rounded-full {{ $color }}" style="width: {{ $percent }}%"></div>
    </div>
    <span class="text-xs font-semibold text-gray-700 dark:text-gray-200">{{ $percent }}%</span>
</div>
@endif
