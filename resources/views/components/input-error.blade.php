@props(['for','alert'=>''])

@error($for)
<p {{ $attributes->merge(['class' => 'text-xs text-red-500 font-bold mt-1']) }}>{{ $message }}</p>
{{-- {{ $this->alertError($alert) }} --}}
@enderror