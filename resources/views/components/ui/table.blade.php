{{-- Framed data table. Pass column headings in the `head` slot (<th> cells) and rows in the default slot. --}}
<div {{ $attributes->merge(['class' => 'table-shell']) }}>
    <table class="table-ui">
        @if (isset($head))
        <thead>
            <tr>{{ $head }}</tr>
        </thead>
        @endif
        <tbody>
            {{ $slot }}
        </tbody>
        @if (isset($foot))
        <tfoot>{{ $foot }}</tfoot>
        @endif
    </table>
</div>
