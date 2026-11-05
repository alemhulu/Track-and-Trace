@props(['for','alert'=>''])

@error($for)
<p {{ $attributes->merge(['class' => 'flex items-center gap-1 text-xs font-medium text-rose-600 dark:text-rose-400 mt-1.5']) }}>{{ $message }}</p>
{{-- Also raise the message as a toast, so an error on a field scrolled out of view is not missed --}}
@if ($alert && isset($this) && method_exists($this, 'alertError'))
{{ $this->alertError($message) }}
@endif
@enderror
