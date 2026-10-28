@props(['title', 'name', 'value' => null, 'checked' => false])
{{-- Selectable option tile: a checkbox with its label, used for multi-select lists such as roles. --}}
<label class="flex items-center gap-2.5 px-3 py-2 border rounded-lg cursor-pointer transition border-slate-200 hover:border-brand-300 hover:bg-brand-50/50 dark:border-slate-700 dark:hover:bg-slate-800">
    <x-checkbox name="{{ $name }}" :value="$value ?? null" :checked="(bool) $checked" />
    <span class="text-sm font-semibold truncate text-slate-700 dark:text-slate-200">{{ $title }}</span>
</label>
