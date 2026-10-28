@props(['label' => null, 'for' => null, 'hint' => null, 'required' => false, 'error' => null])
{{-- Label + control + hint + validation message, laid out the same on every form.
     `error` is the validation key; it defaults to `for`. --}}
<div {{ $attributes }}>
    @if ($label)
    <label @if ($for) for="{{ $for }}" @endif class="form-label">
        {{ $label }}
        @if ($required)<span class="text-rose-500" aria-hidden="true">*</span>@endif
    </label>
    @endif
    {{ $slot }}
    @if ($hint)
    <p class="form-hint">{{ $hint }}</p>
    @endif
    @php $errorKey = $error ?? $for; @endphp
    @if ($errorKey)
    @error($errorKey)
    <p class="mt-1.5 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</p>
    @enderror
    @endif
</div>
