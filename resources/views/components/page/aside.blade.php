@props(['title', 'sticky' => false])
{{-- Vertical Navigation Menu --}}
<aside class="mb-5 lg:mb-0 lg:col-span-3 card overflow-hidden {{ $sticky ? 'lg:sticky lg:top-24 self-start' : '' }}">
    <div class="card-header">
        <h2 class="card-title">{{ $title }}</h2>
    </div>
    <nav class="p-2 space-y-1">
        {{ $slot }}
    </nav>
</aside>
