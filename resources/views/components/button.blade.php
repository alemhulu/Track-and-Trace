@props(['btnType'])
@php
$btnType = [
'secondary' => 'btn-secondary w-full',
'danger' => 'btn-danger w-full',
'info' => 'btn-info w-full',
'warning' => 'btn-warning w-full',
'success' => 'btn-success w-full',
'primary' => 'btn-primary w-full',
][$btnType ?? 'primary'];
@endphp

<button {{ $attributes->merge(['class' => $btnType]) }}>
    {{ $slot }}
</button>
