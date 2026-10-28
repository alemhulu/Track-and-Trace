@extends('print.layout')

@section('title', 'Delivery note ' . $shipment->reference)

@section('content')
<div class="head">
    <div>
        <h1>Textbook Delivery Note and Receipt</h1>
        <div class="muted">Keep one copy with the sender, one with the driver and one with the receiver. Enter the receipt in the system (or the field app) with the counts written below.</div>
    </div>
    <div style="text-align:center">
        {!! DNS2D::getBarcodeSVG($shipment->reference, 'QRCODE', 3, 3) !!}
        <div><strong>{{ $shipment->reference }}</strong></div>
    </div>
</div>

<div class="grid">
    <div><span>From</span>{{ $shipment->organization->name ?? '—' }}</div>
    <div><span>To</span>{{ $shipment->destinationOrganization->name ?? '—' }}</div>
    <div><span>Dispatched</span>{{ optional($shipment->distributed_at)->format('Y-m-d H:i') }} by {{ $shipment->dispatcher->name ?? '—' }}</div>
    <div><span>Carrier</span>{{ $shipment->carrier ?: '—' }}</div>
    <div><span>Waybill</span>{{ $shipment->waybill_no ?: '—' }}</div>
    <div><span>Vehicle / Driver</span>{{ $shipment->vehicle_no ?: '—' }} / {{ $shipment->driver_name ?: '—' }} {{ $shipment->driver_phone }}</div>
</div>

<table>
    <thead>
        <tr>
            <th rowspan="2">Title</th>
            <th colspan="3">Sent</th>
            <th colspan="4">Received (fill in on arrival)</th>
        </tr>
        <tr>
            <th>Packages</th>
            <th>Student books</th>
            <th>Teacher guides</th>
            <th>Student books</th>
            <th>Teacher guides</th>
            <th>Damaged SB</th>
            <th>Damaged TG</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($shipment->lines as $line)
        <tr>
            <td>{{ $line->book->title }}</td>
            <td>{{ $line->full_packages }}{{ $line->partial_package_books || $line->partial_teacher_guides ? ' + 1 partial' : '' }}</td>
            <td>{{ number_format($line->quantity) }}</td>
            <td>{{ number_format($line->teacher_guides) }}</td>
            @if ($shipment->isInTransit())
            <td class="blank"></td><td class="blank"></td><td class="blank"></td><td class="blank"></td>
            @else
            <td>{{ number_format((int) $line->received_quantity) }}</td>
            <td>{{ number_format((int) $line->received_teacher_guides) }}</td>
            <td>{{ (int) $line->damaged_quantity }}</td>
            <td>{{ (int) $line->damaged_teacher_guides }}</td>
            @endif
        </tr>
        @endforeach
        <tr>
            <th>Total</th>
            <th>{{ $shipment->lines->sum(fn ($line) => $line->full_packages + ($line->partial_package_books || $line->partial_teacher_guides ? 1 : 0)) }}</th>
            <th>{{ number_format($shipment->lines->sum('quantity')) }}</th>
            <th>{{ number_format($shipment->lines->sum('teacher_guides')) }}</th>
            <th></th><th></th><th></th><th></th>
        </tr>
    </tbody>
</table>

<p style="margin-top:14px">Arrival date and time: <span class="write"></span> &nbsp; Remarks: <span class="write" style="min-width:320px"></span></p>

<div class="signatures">
    <div>Dispatched by (name, signature, stamp)</div>
    <div>Driver (name, signature)</div>
    <div>Received by (name, signature, stamp)</div>
</div>
@endsection
