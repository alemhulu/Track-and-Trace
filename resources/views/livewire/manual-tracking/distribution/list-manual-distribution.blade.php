<div>
    @php
        $statusClasses = [
            'dispatched' => 'badge-warning',
            'received' => 'badge-success',
            'received_with_discrepancy' => 'badge-danger',
        ];
    @endphp

    <div class="flex flex-col-reverse gap-3 mb-3">
        <div class="grid grid-cols-1 gap-3 md:grid-cols-3 lg:grid-cols-6">
            <x-location-picker :picker="$locationPicker" model="area" organization-model="organizationFilter"
                organization-label="Place" organization-placeholder="All places in my area" />
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
        <div class="flex justify-end">
            <a href="{{ route('manual-tracking.distribution.add') }}"
                class="btn-primary">
                New Shipment
            </a>
        </div>
        @endif
    </div>

    {{-- Searching by reference or waybill is in the filters above --}}
    <x-form.table title="Shipments" :search="false">
        <x-slot name="tableHeaders">
            <x-data-table.th scope="col">{{ __('Reference · Dispatched') }}</x-data-table.th>
            <x-data-table.th scope="col">{{ __('From') }}</x-data-table.th>
            <x-data-table.th scope="col">{{ __('To') }}</x-data-table.th>
            <x-data-table.th scope="col">{{ __('Titles') }}</x-data-table.th>
            <x-data-table.th scope="col" class="th-wrap">{{ __('Sent (SB / TG)') }}</x-data-table.th>
            <x-data-table.th scope="col">{{ __('Status') }}</x-data-table.th>
            <x-data-table.th scope="col"><span class="sr-only">{{ __('View') }}</span></x-data-table.th>
        </x-slot>

        <x-slot name="tableRows">
            @forelse ($distributions as $distribution)
            <x-data-table.tr>
                <td class="px-5 py-2 text-sm whitespace-nowrap">
                    <a href="{{ route('manual-tracking.shipments.show', $distribution->id) }}"
                        class="font-semibold link">{{ $distribution->reference }}</a>
                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ optional($distribution->distributed_at)->format('Y-m-d') ?: '-' }}</div>
                </td>
                <td class="px-5 py-2 text-sm text-gray-700 dark:text-gray-100"><div class="cell-wrap">{{ $distribution->organization->name ?? '-' }}</div></td>
                <td class="px-5 py-2 text-sm text-gray-700 dark:text-gray-100"><div class="cell-wrap">{{ $distribution->destinationOrganization->name ?? '-' }}</div></td>
                <td class="px-5 py-2 text-sm text-gray-700 dark:text-gray-100">
                    <x-ui.clamp title="Titles in {{ $distribution->reference }}">{{ $distribution->lines->pluck('book.title')->filter()->join(', ') ?: '-' }}</x-ui.clamp>
                </td>
                <td class="px-5 py-2 text-sm text-gray-700 whitespace-nowrap dark:text-gray-100">
                    {{ number_format((int) $distribution->lines_sum_quantity) }} / {{ number_format((int) $distribution->lines_sum_teacher_guides) }}
                </td>
                <td class="px-5 py-2 text-sm">
                    <span class="{{ $statusClasses[$distribution->status] ?? 'badge-neutral' }}">
                        {{ \App\Models\ManualTracking\ManualDistribution::STATUSES[$distribution->status] ?? $distribution->status }}
                    </span>
                    @if ($distribution->open_discrepancies_count)
                    <span class="block mt-1 text-xs text-red-600 dark:text-red-400">{{ $distribution->open_discrepancies_count }} open {{ \Illuminate\Support\Str::plural('discrepancy', $distribution->open_discrepancies_count) }}</span>
                    @endif
                </td>
                <td class="px-5 py-2">
                    <x-action.view-link :href="route('manual-tracking.shipments.show', $distribution->id)" label="View shipment" />
                </td>
            </x-data-table.tr>
            @empty
            <x-data-table.empty colspan="7" />
            @endforelse
        </x-slot>

        {{ $distributions->links() }}
    </x-form.table>
</div>
