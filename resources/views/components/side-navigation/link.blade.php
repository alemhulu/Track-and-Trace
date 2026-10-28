@props(['active', 'name', 'icon'])

<a {{ $attributes->class(['nav-item', 'nav-item-active' => $active ?? false]) }}
    @if ($active ?? false) aria-current="page" @endif>
    <i class="fi {{ $icon ?? '' }} flex text-lg leading-none {{ ($active ?? false) ? 'text-gold-300' : 'text-brand-300' }}"></i>
    <span class="truncate">{{ $name ?? $slot }}</span>
</a>
