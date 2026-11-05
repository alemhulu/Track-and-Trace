@props(['href', 'label' => 'View'])
{{-- Eye button for a table row that opens the record's own page --}}
<a href="{{ $href }}" {{ $attributes->merge(['class' => 'icon-btn-view']) }} title="{{ $label }}" aria-label="{{ $label }}">
    <i class="flex fi fi-rr-eye"></i>
</a>
