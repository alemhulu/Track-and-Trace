@props(['title' => null, 'subtitle' => null, 'icon' => null, 'flush' => false])
{{-- Standard surface. Named slots: `actions` (header, right), `footer` (bottom bar).
     `flush` drops the body padding, e.g. for tables that should touch the card edges. --}}
<section {{ $attributes->merge(['class' => 'card overflow-hidden']) }}>
    @if ($title || isset($actions))
    <header class="card-header">
        <div class="flex items-center min-w-0 gap-3">
            @if ($icon)
            <span class="flex items-center justify-center w-9 h-9 rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-900/40 dark:text-brand-300">
                <i class="flex fi {{ $icon }}"></i>
            </span>
            @endif
            <div class="min-w-0">
                @if ($title)
                <h3 class="card-title">{{ $title }}</h3>
                @endif
                @if ($subtitle)
                <p class="card-subtitle">{{ $subtitle }}</p>
                @endif
            </div>
        </div>
        @if (isset($actions))
        <div class="flex flex-wrap items-center gap-2">{{ $actions }}</div>
        @endif
    </header>
    @endif

    <div @class(['card-body' => ! $flush])>
        {{ $slot }}
    </div>

    @if (isset($footer))
    <footer class="card-footer">{{ $footer }}</footer>
    @endif
</section>
