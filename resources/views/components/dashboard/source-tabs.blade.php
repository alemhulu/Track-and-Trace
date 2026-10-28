{{-- Tab bar for the Manual / Digital dashboard panels. Expects an Alpine `tab` variable in scope. --}}
<div role="tablist" aria-label="Tracking source"
    {{ $attributes->merge(['class' => 'inline-flex p-1 bg-gray-100 rounded-lg dark:bg-gray-800']) }}>
    @foreach (['manual' => 'Manual Tracking', 'digital' => 'Digital Tracking'] as $key => $label)
    <button type="button" role="tab" id="tab-{{ $key }}" aria-controls="panel-{{ $key }}"
        :aria-selected="(tab === '{{ $key }}').toString()" @click="tab = '{{ $key }}'"
        :class="tab === '{{ $key }}'
            ? 'bg-white text-gray-900 shadow dark:bg-gray-700 dark:text-white'
            : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'"
        class="px-4 py-2 text-sm font-semibold transition rounded-md focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500">
        {{ $label }}
    </button>
    @endforeach
</div>
