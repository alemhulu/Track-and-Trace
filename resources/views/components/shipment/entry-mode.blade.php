@props(['model', 'value'])
{{-- Count by package, or type the total copies when counting packages is impractical --}}
<div class="inline-flex p-0.5 text-xs bg-gray-100 rounded-md dark:bg-gray-700" role="group" aria-label="How to count">
    @foreach (['packages' => 'By packages', 'totals' => 'By total copies'] as $mode => $label)
    <button type="button" wire:click="$set('{{ $model }}', '{{ $mode }}')"
        class="px-3 py-1 rounded {{ $value === $mode ? 'bg-white shadow-sm font-semibold text-gray-900 dark:bg-gray-900 dark:text-gray-100' : 'text-gray-600 dark:text-gray-300' }}">
        {{ $label }}
    </button>
    @endforeach
</div>
