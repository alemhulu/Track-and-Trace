@foreach ($codes as $code)
<div>
    <span class="font-medium">{{ \App\Models\ManualTracking\ManualBook::EDITIONS[$code->edition] ?? $code->edition }}:</span>
    {{ $code->payload }}{{ $code->print_code ? ' · ' . $code->print_code : '' }}
</div>
@endforeach
