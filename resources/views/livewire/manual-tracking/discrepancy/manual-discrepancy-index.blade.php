<div class="space-y-4">
    @php $types = \App\Http\Livewire\ManualTracking\Discrepancy\ManualDiscrepancyIndex::TYPES; @endphp

    <div class="grid grid-cols-2 gap-3 xl:grid-cols-4">
        <x-ui.stat label="Open" :value="number_format($stats['open'])" icon="fi-rr-shield-exclamation" tone="danger"
            :active="$statusFilter === 'open'" wire:click="setFilter('statusFilter', 'open')">
            {{ number_format($stats['open_copies']) }} copies unresolved
        </x-ui.stat>
        <x-ui.stat label="Resolved" :value="number_format($stats['resolved'])" icon="fi-rr-check" tone="success"
            :active="$statusFilter === 'resolved'" wire:click="setFilter('statusFilter', 'resolved')"
            :percent="$stats['open'] + $stats['resolved'] ? round($stats['resolved'] / ($stats['open'] + $stats['resolved']) * 100, 1) : 0">
            {{ number_format($stats['resolved_copies']) }} copies settled
        </x-ui.stat>
        <x-ui.stat label="Time to resolve" :value="$stats['resolve_days'] !== null ? $stats['resolve_days'] . ' days' : '—'" icon="fi-rr-time-check" tone="info">
            Average from report to resolution
        </x-ui.stat>
        <x-ui.stat label="Oldest open" :value="$stats['oldest_open_days'] !== null ? number_format($stats['oldest_open_days']) . ' days' : '—'" icon="fi-rr-hourglass" tone="warning">
            {{ $stats['oldest_open_days'] !== null ? 'Waiting since it was raised' : 'Nothing waiting' }}
        </x-ui.stat>
    </div>

    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
        @foreach (['missing' => ['warning', 'fi-rr-inbox'], 'damaged' => ['danger', 'fi-rr-exclamation'], 'excess' => ['info', 'fi-rr-box']] as $type => [$tone, $icon])
        <x-ui.stat :label="$types[$type]" :value="number_format($stats['types'][$type]['total'])" :tone="$tone" :icon="$icon"
            :active="$typeFilter === $type" wire:click="setFilter('typeFilter', '{{ $type }}')">
            {{ number_format($stats['types'][$type]['copies']) }} copies{{ $statusFilter ? ' · ' . ucfirst($statusFilter) : '' }} · {{ $typeFilter === $type ? 'click to clear' : 'click to filter' }}
        </x-ui.stat>
        @endforeach
    </div>

    <div class="p-4 card">
        <div class="grid grid-cols-2 gap-3 md:grid-cols-5">
            <div>
                <x-jet-label for="statusFilter" value="Status" />
                <x-form.select wire:model="statusFilter" id="statusFilter">
                    <option value="open">Open</option>
                    <option value="resolved">Resolved</option>
                    <option value="">All</option>
                </x-form.select>
            </div>
            <div>
                <x-jet-label for="typeFilter" value="Issue" />
                <x-form.select wire:model="typeFilter" id="typeFilter">
                    <option value="">All issues</option>
                    @foreach ($types as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </x-form.select>
            </div>
            <div>
                <x-jet-label for="editionFilter" value="Edition" />
                <x-form.select wire:model="editionFilter" id="editionFilter">
                    <option value="">Both</option>
                    @foreach (\App\Models\ManualTracking\ManualBook::EDITIONS as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </x-form.select>
            </div>
            <div>
                <x-jet-label for="dateFrom" value="Raised from" />
                <x-jet-input wire:model="dateFrom" id="dateFrom" type="date" class="block w-full mt-1" />
            </div>
            <div>
                <x-jet-label for="dateTo" value="Raised to" />
                <x-jet-input wire:model="dateTo" id="dateTo" type="date" class="block w-full mt-1" />
            </div>
        </div>
        @if ($filtersActive)
        <div class="flex justify-end mt-3">
            <button type="button" wire:click="clearFilters" class="text-sm link">Show everything</button>
        </div>
        @endif
    </div>

    <x-form.table title="Discrepancies" :subtitle="number_format($discrepancies->total()) . ' ' . \Illuminate\Support\Str::plural('discrepancy', $discrepancies->total()) . ' · search by shipment, title or place'">
        <x-slot name="tableHeaders">
            <x-data-table.th scope="col">{{ __('Shipment') }}</x-data-table.th>
            <x-data-table.th scope="col">{{ __('Title') }}</x-data-table.th>
            <x-data-table.th scope="col">{{ __('Issue') }}</x-data-table.th>
            <x-data-table.th scope="col">{{ __('Raised') }}</x-data-table.th>
            <x-data-table.th scope="col">{{ __('Resolution') }}</x-data-table.th>
            <x-data-table.th scope="col"><span class="sr-only">{{ __('View') }}</span></x-data-table.th>
        </x-slot>

        <x-slot name="tableRows">
            @forelse ($discrepancies as $discrepancy)
            @php
                $shipment = $discrepancy->line->distribution;
                // The sender and the receiver may not close their own hand-off
                $ownHandOff = in_array((int) auth()->id(), [(int) $shipment->distributed_by, (int) $shipment->received_by], true);
            @endphp
            <x-data-table.tr wire:key="discrepancy-{{ $discrepancy->id }}" class="align-top">
                <td class="px-5 py-2 text-sm">
                    <a href="{{ route('manual-tracking.shipments.show', $shipment->id) }}"
                        class="font-semibold whitespace-nowrap link">{{ $shipment->reference }}</a>
                    <x-ui.clamp class="text-xs text-gray-500 dark:text-gray-400" title="Shipment {{ $shipment->reference }}">
                        {{ $shipment->organization->name ?? '—' }} → {{ $shipment->destinationOrganization->name ?? '—' }}
                    </x-ui.clamp>
                </td>
                <td class="px-5 py-2 text-sm text-gray-700 dark:text-gray-100">
                    <x-ui.clamp title="Title">{{ $discrepancy->line->book->title ?? '—' }}</x-ui.clamp>
                </td>
                <td class="px-5 py-2 text-sm whitespace-nowrap">
                    <span class="font-semibold {{ $discrepancy->status === 'open' ? 'text-red-600 dark:text-red-400' : 'text-gray-600 dark:text-gray-300' }}">
                        {{ ucfirst($discrepancy->type) }} {{ number_format($discrepancy->quantity) }}
                    </span>
                    <span class="block text-xs text-gray-500 dark:text-gray-400">{{ \App\Models\ManualTracking\ManualBook::EDITIONS[$discrepancy->edition] ?? $discrepancy->edition }}</span>
                </td>
                <td class="px-5 py-2 text-xs text-gray-500 whitespace-nowrap dark:text-gray-400">
                    {{ $discrepancy->created_at->format('Y-m-d') }}<br>by {{ $discrepancy->raisedBy->name ?? '—' }}
                </td>
                <td class="px-5 py-2 text-sm">
                    @if ($discrepancy->status === 'open' && ! auth()->user()->can('manual-tracking.discrepancies.resolve'))
                    <span class="text-xs text-gray-500 dark:text-gray-400">Awaiting resolution</span>
                    @elseif ($discrepancy->status === 'open' && $ownHandOff)
                    <span class="text-xs text-gray-500 dark:text-gray-400">Awaiting review by someone other than the sender and receiver</span>
                    @elseif ($discrepancy->status === 'open')
                    <div class="grid grid-cols-1 gap-2 min-w-[14rem] max-w-xs">
                        <x-form.select wire:model.defer="resolutions.{{ $discrepancy->id }}">
                            <option value="">Choose resolution</option>
                            @foreach (\App\Models\ManualTracking\ManualDiscrepancy::RESOLUTIONS[$discrepancy->type] ?? [] as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </x-form.select>
                        <div class="flex items-center gap-2">
                            <x-jet-input wire:model.defer="notes.{{ $discrepancy->id }}" type="text" placeholder="Notes (optional)" class="flex-1 min-w-0" />
                            <button type="button" wire:click="resolve({{ $discrepancy->id }})" wire:loading.attr="disabled"
                                class="mt-1 btn-primary btn-sm">Resolve</button>
                        </div>
                    </div>
                    @else
                    <div class="text-gray-700 dark:text-gray-200">
                        {{ \App\Models\ManualTracking\ManualDiscrepancy::RESOLUTIONS[$discrepancy->type][$discrepancy->resolution] ?? $discrepancy->resolution }}
                    </div>
                    <x-ui.clamp class="text-xs text-gray-500 dark:text-gray-400" title="Resolution notes">
                        {{ optional($discrepancy->resolved_at)->format('Y-m-d') }} by {{ $discrepancy->resolvedBy->name ?? '—' }}
                        {{ $discrepancy->resolution_notes ? '· ' . $discrepancy->resolution_notes : '' }}
                    </x-ui.clamp>
                    @endif
                </td>
                <td class="px-5 py-2">
                    <x-action.view-link :href="route('manual-tracking.shipments.show', $shipment->id)" label="View shipment" />
                </td>
            </x-data-table.tr>
            @empty
            <x-data-table.empty colspan="6" />
            @endforelse
        </x-slot>

        {{ $discrepancies->links() }}
    </x-form.table>
</div>
