@props(['tone' => 'neutral', 'dot' => false])
{{-- tone: brand | gold | success | warning | danger | info | neutral --}}
<span {{ $attributes->merge(['class' => 'badge-' . $tone]) }}>
    @if ($dot)
    <span class="w-1.5 h-1.5 rounded-full bg-current opacity-70"></span>
    @endif
    {{ $slot }}
</span>
