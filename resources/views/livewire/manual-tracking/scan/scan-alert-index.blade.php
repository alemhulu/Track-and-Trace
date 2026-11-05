<div class="space-y-4">
    <div>
        <h3 class="section-title">Misrouted packages</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400">Raised when a package or book is scanned somewhere it does not belong.</p>
    </div>

    <div class="grid grid-cols-2 gap-3 xl:grid-cols-4">
        <x-ui.stat label="Open" :value="number_format($stats['open'])" icon="fi-rr-shield-exclamation" tone="danger"
            :active="$statusFilter === 'open'" wire:click="setFilter('statusFilter', 'open')">
            At {{ number_format($stats['open_places']) }} {{ \Illuminate\Support\Str::plural('place', $stats['open_places']) }} · {{ number_format($stats['open_shipments']) }} {{ \Illuminate\Support\Str::plural('shipment', $stats['open_shipments']) }}
        </x-ui.stat>
        <x-ui.stat label="Closed" :value="number_format($stats['resolved'])" icon="fi-rr-check" tone="success"
            :active="$statusFilter === 'resolved'" wire:click="setFilter('statusFilter', 'resolved')"
            :percent="$stats['open'] + $stats['resolved'] ? round($stats['resolved'] / ($stats['open'] + $stats['resolved']) * 100, 1) : 0">
            {{ $stats['close_days'] !== null ? 'Closed in ' . $stats['close_days'] . ' days on average' : 'None closed yet' }}
        </x-ui.stat>
        @foreach ([\App\Models\ManualTracking\ManualScan::FLAG_MISROUTE => ['warning', 'fi-rr-shuffle'], \App\Models\ManualTracking\ManualScan::FLAG_WRONG_REGION => ['info', 'fi-rr-map-marker']] as $flag => [$tone, $icon])
        <x-ui.stat :label="\App\Models\ManualTracking\ManualScan::FLAG_LABELS[$flag]" :value="number_format($stats['flags'][$flag] ?? 0)" :tone="$tone" :icon="$icon"
            :active="$flagFilter === $flag" wire:click="setFilter('flagFilter', '{{ $flag }}')">
            {{ $statusFilter === 'open' ? 'Open' : ($statusFilter ? 'Closed' : 'All') }} alerts · {{ $flagFilter === $flag ? 'click to clear' : 'click to filter' }}
        </x-ui.stat>
        @endforeach
    </div>

    <div class="p-4 card">
        <div class="grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-6">
            <div class="col-span-2">
                <x-jet-label for="alert-search" value="Search" />
                <x-jet-input wire:model.debounce.400ms="search" id="alert-search" type="search" placeholder="Place, title, shipment or scanned code" class="block w-full mt-1" />
            </div>
            <div>
                <x-jet-label for="alert-status" value="Status" />
                <x-form.select wire:model="statusFilter" id="alert-status">
                    <option value="open">Open</option>
                    <option value="resolved">Closed</option>
                    <option value="">All</option>
                </x-form.select>
            </div>
            <div>
                <x-jet-label for="alert-context" value="Found while" />
                <x-form.select wire:model="contextFilter" id="alert-context">
                    <option value="">Sorting or receiving</option>
                    <option value="sort">Sorting</option>
                    <option value="receive">Receiving</option>
                </x-form.select>
            </div>
            <div>
                <x-jet-label for="alert-from" value="Scanned from" />
                <x-jet-input wire:model="dateFrom" id="alert-from" type="date" class="block w-full mt-1" />
            </div>
            <div>
                <x-jet-label for="alert-to" value="Scanned to" />
                <x-jet-input wire:model="dateTo" id="alert-to" type="date" class="block w-full mt-1" />
            </div>
        </div>
        <div class="flex flex-wrap items-center justify-between gap-2 mt-3">
            <p class="text-xs text-gray-500 dark:text-gray-400">{{ number_format($alerts->total()) }} {{ \Illuminate\Support\Str::plural('alert', $alerts->total()) }} shown</p>
            @if ($filtersActive)
            <button type="button" wire:click="clearFilters" class="text-sm link">Show everything</button>
            @endif
        </div>
    </div>

    <div class="overflow-x-auto card">
        <table class="table-ui">
            <thead>
                <tr>
                    <th class="px-4 py-2">Scanned</th>
                    <th class="px-4 py-2">Where</th>
                    <th class="px-4 py-2">Problem</th>
                    <th class="px-4 py-2">Shipment</th>
                    <th class="px-4 py-2">Action</th>
                    <th class="px-4 py-2"><span class="sr-only">View</span></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($alerts as $alert)
                <tr wire:key="alert-{{ $alert->id }}" class="align-top">
                    <td class="px-4 py-2 text-gray-700 whitespace-nowrap dark:text-gray-200">
                        {{ $alert->scanned_at->format('Y-m-d H:i') }}
                        <div class="text-xs text-gray-500">by {{ $alert->scanner->name ?? '—' }}</div>
                    </td>
                    <td class="px-4 py-2 text-gray-700 dark:text-gray-200">
                        <div class="cell-wrap">{{ $alert->organization->name ?? '—' }}</div>
                        <div class="text-xs text-gray-500">{{ $alert->context === 'receive' ? 'Receiving' : 'Sorting' }}</div>
                    </td>
                    <td class="px-4 py-2">
                        <div class="font-semibold text-red-700 dark:text-red-400">{{ \App\Models\ManualTracking\ManualScan::FLAG_LABELS[$alert->flag] ?? $alert->flag }}</div>
                        <x-ui.clamp class="text-gray-700 dark:text-gray-200" title="Misroute details">{{ $alert->flag_message }}</x-ui.clamp>
                        @if ($alert->book)
                        <div class="text-xs text-gray-500">{{ $alert->book->title }}</div>
                        @endif
                    </td>
                    <td class="px-4 py-2">
                        @if ($alert->distribution)
                        <a href="{{ route('manual-tracking.shipments.show', $alert->distribution->id) }}"
                            class="font-medium link">{{ $alert->distribution->reference }}</a>
                        <div class="text-xs text-gray-500">from {{ $alert->distribution->organization->name ?? '—' }}</div>
                        @else
                        <span class="text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-2 min-w-[16rem]">
                        @if ($alert->alert_status === 'open' && ! auth()->user()->can('manual-tracking.alerts.resolve'))
                        <span class="text-xs text-gray-500">Open</span>
                        @elseif ($alert->alert_status === 'open' && (int) $alert->scanned_by === (int) auth()->id())
                        <span class="text-xs text-gray-500 dark:text-gray-400">Awaiting someone other than you, the scanner, to close it</span>
                        @elseif ($alert->alert_status === 'open')
                        <div class="flex gap-2">
                            <x-jet-input wire:model.defer="notes.{{ $alert->id }}" type="text" placeholder="What was done, e.g. sent on to Arsi Zone" class="flex-1 text-sm" />
                            <button type="button" wire:click="resolve({{ $alert->id }})"
                                class="btn-primary btn-sm">Close</button>
                        </div>
                        @else
                        <x-ui.clamp class="text-gray-700 dark:text-gray-200" title="How it was closed">{{ $alert->resolution_note }}</x-ui.clamp>
                        <div class="text-xs text-gray-500">{{ $alert->resolver->name ?? '—' }}, {{ optional($alert->resolved_at)->format('Y-m-d') }}</div>
                        @endif
                    </td>
                    <td class="px-4 py-2">
                        @if ($alert->distribution)
                        <x-action.view-link :href="route('manual-tracking.shipments.show', $alert->distribution->id)" label="View shipment" />
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-4 py-6 text-center text-gray-500">No misroute alerts.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $alerts->links() }}
</div>
