<div>
    @php
        $statusClasses = [
            'dispatched' => 'badge-warning',
            'received' => 'badge-success',
            'received_with_discrepancy' => 'badge-danger',
        ];
    @endphp

    <div class="flex flex-col gap-3 mb-3 md:flex-row md:items-end md:justify-between">
        <div class="grid grid-cols-1 gap-3 md:grid-cols-2 md:w-2/3">
            <div>
                <x-jet-label for="search" value="Reference or waybill" />
                <x-jet-input wire:model.debounce.400ms="search" id="search" type="search" class="block w-full mt-1" />
            </div>
            <div>
                <x-jet-label for="statusFilter" value="Status" />
                <x-form.select wire:model="statusFilter" id="statusFilter">
                    <option value="">All statuses</option>
                    @foreach (\App\Models\ManualTracking\ManualDistribution::STATUSES as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </x-form.select>
            </div>
        </div>

        @if ($canCreate)
        <a href="{{ route('manual-tracking.distribution.add') }}"
            class="btn-primary">
            New Shipment
        </a>
        @endif
    </div>

    <x-form.table title="Shipments">
        <x-slot name="tableHeaders">
            <x-data-table.th scope="col">{{ __('Reference') }}</x-data-table.th>
            <x-data-table.th scope="col">{{ __('Dispatched') }}</x-data-table.th>
            <x-data-table.th scope="col">{{ __('From') }}</x-data-table.th>
            <x-data-table.th scope="col">{{ __('To') }}</x-data-table.th>
            <x-data-table.th scope="col">{{ __('Titles') }}</x-data-table.th>
            <x-data-table.th scope="col">{{ __('Sent (SB / TG)') }}</x-data-table.th>
            <x-data-table.th scope="col">{{ __('Status') }}</x-data-table.th>
        </x-slot>

        <x-slot name="tableRows">
            @forelse ($distributions as $distribution)
            <x-data-table.tr>
                <td class="px-5 py-2 text-sm font-semibold whitespace-nowrap">
                    <a href="{{ route('manual-tracking.shipments.show', $distribution->id) }}"
                        class="link">{{ $distribution->reference }}</a>
                </td>
                <td class="px-5 py-2 text-sm text-gray-700 whitespace-nowrap dark:text-gray-100">
                    {{ optional($distribution->distributed_at)->format('Y-m-d') ?: '-' }}
                </td>
                <td class="px-5 py-2 text-sm text-gray-700 whitespace-nowrap dark:text-gray-100">{{ $distribution->organization->name ?? '-' }}</td>
                <td class="px-5 py-2 text-sm text-gray-700 whitespace-nowrap dark:text-gray-100">{{ $distribution->destinationOrganization->name ?? '-' }}</td>
                <td class="px-5 py-2 text-sm text-gray-700 dark:text-gray-100">
                    {{ $distribution->lines->pluck('book.title')->filter()->join(', ') ?: '-' }}
                </td>
                <td class="px-5 py-2 text-sm text-gray-700 whitespace-nowrap dark:text-gray-100">
                    {{ number_format((int) $distribution->lines_sum_quantity) }} / {{ number_format((int) $distribution->lines_sum_teacher_guides) }}
                </td>
                <td class="px-5 py-2 text-sm whitespace-nowrap">
                    <span class="{{ $statusClasses[$distribution->status] ?? 'badge-neutral' }}">
                        {{ \App\Models\ManualTracking\ManualDistribution::STATUSES[$distribution->status] ?? $distribution->status }}
                    </span>
                    @if ($distribution->open_discrepancies_count)
                    <span class="ml-1 text-xs text-red-600">{{ $distribution->open_discrepancies_count }} open</span>
                    @endif
                </td>
            </x-data-table.tr>
            @empty
            <x-data-table.empty colspan="7" />
            @endforelse
        </x-slot>

        {{ $distributions->links() }}
    </x-form.table>
</div>
