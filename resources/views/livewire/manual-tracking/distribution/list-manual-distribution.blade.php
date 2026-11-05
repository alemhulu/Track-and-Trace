<div>
    @php
        $statusClasses = [
            'dispatched' => 'badge-warning',
            'received' => 'badge-success',
            'received_with_discrepancy' => 'badge-danger',
        ];
        $sent = $stats['student'] + $stats['teacher'];
        $receivedCopies = $stats['received_student'] + $stats['received_teacher'];
    @endphp

    <div class="grid grid-cols-2 gap-3 mb-3 xl:grid-cols-4">
        <x-ui.stat label="Shipments" :value="number_format($stats['total'])" icon="fi-rr-truck-side"
            wire:click="filterStatus('')">
            {{ $filtersActive ? 'Matching the filters' : 'All shipments in your area' }}
        </x-ui.stat>
        <x-ui.stat label="In transit" :value="number_format($stats['status']['dispatched'])" icon="fi-rr-time-past" tone="warning"
            :active="$statusFilter === 'dispatched'" wire:click="filterStatus('dispatched')">
            {{ $stats['overdue'] ? number_format($stats['overdue']) . ' past expected arrival' : 'None overdue' }}
        </x-ui.stat>
        <x-ui.stat label="Received" :value="number_format($stats['status']['received'])" icon="fi-rr-check" tone="success"
            :active="$statusFilter === 'received'" wire:click="filterStatus('received')"
            :percent="$stats['total'] ? round(($stats['status']['received'] + $stats['status']['received_with_discrepancy']) / $stats['total'] * 100, 1) : 0">
            {{ $stats['transit_days'] !== null ? 'Average ' . $stats['transit_days'] . ' days on the road' : 'No receipts yet' }}
        </x-ui.stat>
        <x-ui.stat label="With discrepancy" :value="number_format($stats['status']['received_with_discrepancy'])" icon="fi-rr-shield-exclamation" tone="danger"
            :active="$statusFilter === 'received_with_discrepancy'" wire:click="filterStatus('received_with_discrepancy')">
            {{ number_format($stats['open_discrepancies']) }} {{ \Illuminate\Support\Str::plural('shipment', $stats['open_discrepancies']) }} with open discrepancies
        </x-ui.stat>
    </div>

    <div class="grid grid-cols-1 gap-3 mb-3 sm:grid-cols-3">
        <x-ui.stat label="Student books sent" :value="number_format($stats['student'])" icon="fi-rr-graduation-cap" tone="info"
            :percent="$stats['student'] ? round($stats['received_student'] / $stats['student'] * 100, 1) : 0">
            {{ number_format($stats['received_student']) }} counted on receipt{{ $statusFilter ? ' · ' . \App\Models\ManualTracking\ManualDistribution::STATUSES[$statusFilter] : '' }}
        </x-ui.stat>
        <x-ui.stat label="Teacher guides sent" :value="number_format($stats['teacher'])" icon="fi-rr-book-alt" tone="info"
            :percent="$stats['teacher'] ? round($stats['received_teacher'] / $stats['teacher'] * 100, 1) : 0">
            {{ number_format($stats['received_teacher']) }} counted on receipt
        </x-ui.stat>
        <x-ui.stat label="Damaged on arrival" :value="number_format($stats['damaged'])" icon="fi-rr-exclamation" tone="danger">
            {{ $sent ? round($stats['damaged'] / $sent * 100, 2) . '% of copies sent' : 'Nothing sent' }} · {{ number_format(max(0, $sent - $receivedCopies)) }} not yet counted in
        </x-ui.stat>
    </div>

    <div class="flex flex-col gap-3 p-4 mb-3 card">
        <div class="grid grid-cols-1 gap-3 md:grid-cols-3 lg:grid-cols-6">
            <x-location-picker :picker="$locationPicker" model="area" organization-model="organizationFilter"
                organization-label="Place" organization-placeholder="All places in my area" />
            <div>
                <x-jet-label for="direction" value="Direction" />
                <x-form.select wire:model="direction" id="direction" :disabled="! $placeChosen" title="Choose a place first">
                    <option value="">Leaving or arriving</option>
                    <option value="outgoing">Leaving the place</option>
                    <option value="incoming">Arriving at the place</option>
                </x-form.select>
            </div>
            <div>
                <x-jet-label for="search" value="Search" />
                <x-jet-input wire:model.debounce.400ms="search" id="search" type="search" placeholder="Reference, waybill, vehicle, driver, place" class="block w-full mt-1" />
            </div>
            <div>
                <x-jet-label for="bookFilter" value="Title" />
                <x-form.select wire:model="bookFilter" id="bookFilter">
                    <option value="">All titles</option>
                    @foreach ($books as $book)
                    <option value="{{ $book->id }}">{{ $book->title }}</option>
                    @endforeach
                </x-form.select>
            </div>
            <div>
                <x-jet-label for="dateFrom" value="Dispatched from" />
                <x-jet-input wire:model="dateFrom" id="dateFrom" type="date" class="block w-full mt-1" />
            </div>
            <div>
                <x-jet-label for="dateTo" value="Dispatched to" />
                <x-jet-input wire:model="dateTo" id="dateTo" type="date" class="block w-full mt-1" />
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
            <div class="flex flex-col justify-end gap-1.5 lg:col-span-2">
                <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-200">
                    <input type="checkbox" wire:model="overdueOnly" class="rounded border-gray-300 dark:border-gray-600">
                    Only overdue (past expected arrival)
                </label>
                <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-200">
                    <input type="checkbox" wire:model="openDiscrepanciesOnly" class="rounded border-gray-300 dark:border-gray-600">
                    Only with open discrepancies
                </label>
            </div>
        </div>

        <div class="flex flex-wrap items-center justify-end gap-3">
            @if ($filtersActive)
            <button type="button" wire:click="clearFilters" class="text-sm link">Clear filters</button>
            @endif
            @if ($canCreate)
            <a href="{{ route('manual-tracking.distribution.add') }}" class="btn-primary">New Shipment</a>
            @endif
        </div>
    </div>

    {{-- Searching by reference or waybill is in the filters above --}}
    <x-form.table title="Shipments" :search="false"
        :subtitle="number_format($distributions->total()) . ' ' . \Illuminate\Support\Str::plural('shipment', $distributions->total()) . ($statusFilter ? ' · ' . \App\Models\ManualTracking\ManualDistribution::STATUSES[$statusFilter] : '')">
        <x-slot name="tableHeaders">
            <x-data-table.th scope="col">{{ __('Reference · Dispatched') }}</x-data-table.th>
            <x-data-table.th scope="col">{{ __('From') }}</x-data-table.th>
            <x-data-table.th scope="col">{{ __('To') }}</x-data-table.th>
            <x-data-table.th scope="col">{{ __('Titles') }}</x-data-table.th>
            <x-data-table.th scope="col" class="th-wrap">{{ __('Sent (SB / TG)') }}</x-data-table.th>
            <x-data-table.th scope="col" class="th-wrap">{{ __('Received (SB / TG)') }}</x-data-table.th>
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
                <td class="px-5 py-2 text-sm text-gray-700 whitespace-nowrap dark:text-gray-100">
                    @if ($distribution->isInTransit())
                    <span class="text-xs text-gray-400">—</span>
                    @if ($distribution->expected_at)
                    <span class="block text-xs {{ $distribution->expected_at->isPast() ? 'text-red-600 dark:text-red-400 font-semibold' : 'text-gray-500 dark:text-gray-400' }}">
                        {{ $distribution->expected_at->isPast() ? 'Overdue since' : 'Expected' }} {{ $distribution->expected_at->format('Y-m-d') }}
                    </span>
                    @endif
                    @else
                    {{ number_format((int) $distribution->lines_sum_received_quantity) }} / {{ number_format((int) $distribution->lines_sum_received_teacher_guides) }}
                    <span class="block text-xs text-gray-500 dark:text-gray-400">{{ optional($distribution->received_at)->format('Y-m-d') }}</span>
                    @endif
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
            <x-data-table.empty colspan="8" />
            @endforelse
        </x-slot>

        {{ $distributions->links() }}
    </x-form.table>
</div>
