@props(['title', 'name', 'value' => null, 'checked' => false, 'disabled' => false])
{{-- Selectable option tile: a checkbox with its label, used for multi-select lists such as roles. --}}
<label @class([
    'flex items-center gap-2.5 px-3 py-2 border rounded-lg transition border-slate-200 dark:border-slate-700',
    'cursor-pointer hover:border-brand-300 hover:bg-brand-50/50 dark:hover:bg-slate-800' => ! $disabled,
    'opacity-60 cursor-not-allowed' => $disabled,
]) @if ($disabled) title="{{ $attributes->get('reason') }}" @endif>
    <x-checkbox :name="$disabled ? null : $name" :value="$value ?? null" :checked="(bool) $checked" :disabled="(bool) $disabled" />
    <span class="text-sm font-semibold truncate text-slate-700 dark:text-slate-200">{{ $title }}</span>
</label>
