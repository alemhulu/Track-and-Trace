{{-- Tab bar for the Manual / Digital dashboard panels. Expects an Alpine `tab` variable in scope. --}}
<div role="tablist" aria-label="Tracking source"
    {{ $attributes->merge(['class' => 'inline-flex p-1 rounded-xl bg-slate-200/60 dark:bg-slate-800']) }}>
    @foreach (['manual' => ['Manual Tracking', 'fi-rr-cursor-finger'], 'digital' => ['Digital Tracking', 'fi-rr-laptop']] as $key => [$label, $icon])
    <button type="button" role="tab" id="tab-{{ $key }}" aria-controls="panel-{{ $key }}"
        :aria-selected="(tab === '{{ $key }}').toString()" @click="tab = '{{ $key }}'"
        :class="tab === '{{ $key }}'
            ? 'bg-white text-brand-700 shadow-sm dark:bg-slate-700 dark:text-white'
            : 'text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200'"
        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold transition rounded-lg focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500">
        <i class="flex fi {{ $icon }}"></i>
        {{ $label }}
    </button>
    @endforeach
</div>
