@props(['icon' => 'fi-rr-inbox', 'title' => 'No records found', 'message' => 'Try adjusting your search or filters.'])
{{-- Centered placeholder for empty lists. The default slot holds an optional call to action. --}}
<div {{ $attributes->merge(['class' => 'flex flex-col items-center px-6 py-12 text-center']) }}>
    <span class="flex items-center justify-center w-12 h-12 mb-3 rounded-full bg-brand-50 text-brand-500 dark:bg-brand-900/40 dark:text-brand-300">
        <i class="flex text-xl fi {{ $icon }}"></i>
    </span>
    <p class="text-sm font-bold text-slate-800 dark:text-slate-100">{{ $title }}</p>
    @if ($message)
    <p class="max-w-sm mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $message }}</p>
    @endif
    @if (trim($slot) !== '')
    <div class="mt-4">{{ $slot }}</div>
    @endif
</div>
