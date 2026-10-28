<div>
    <div class="mb-3 md:w-1/3">
        <x-jet-label for="statusFilter" value="Status" />
        <x-form.select wire:model="statusFilter" id="statusFilter">
            <option value="open">Open</option>
            <option value="resolved">Resolved</option>
            <option value="">All</option>
        </x-form.select>
    </div>

    <x-form.table title="Discrepancies">
        <x-slot name="tableHeaders">
            <x-data-table.th scope="col">{{ __('Shipment') }}</x-data-table.th>
            <x-data-table.th scope="col">{{ __('Title') }}</x-data-table.th>
            <x-data-table.th scope="col">{{ __('Issue') }}</x-data-table.th>
            <x-data-table.th scope="col">{{ __('Raised') }}</x-data-table.th>
            <x-data-table.th scope="col">{{ __('Resolution') }}</x-data-table.th>
        </x-slot>

        <x-slot name="tableRows">
            @forelse ($discrepancies as $discrepancy)
            @php $shipment = $discrepancy->line->distribution; @endphp
            <x-data-table.tr wire:key="discrepancy-{{ $discrepancy->id }}">
                <td class="px-5 py-2 text-sm whitespace-nowrap">
                    <a href="{{ route('manual-tracking.shipments.show', $shipment->id) }}"
                        class="font-semibold link">{{ $shipment->reference }}</a>
                    <div class="text-xs text-gray-500">
                        {{ $shipment->organization->name ?? '—' }} → {{ $shipment->destinationOrganization->name ?? '—' }}
                    </div>
                </td>
                <td class="px-5 py-2 text-sm text-gray-700 dark:text-gray-100">{{ $discrepancy->line->book->title ?? '—' }}</td>
                <td class="px-5 py-2 text-sm whitespace-nowrap">
                    <span class="font-semibold {{ $discrepancy->status === 'open' ? 'text-red-700' : 'text-gray-600' }}">
                        {{ ucfirst($discrepancy->type) }} {{ number_format($discrepancy->quantity) }}
                    </span>
                    <span class="text-gray-500">{{ \App\Models\ManualTracking\ManualBook::EDITIONS[$discrepancy->edition] ?? $discrepancy->edition }}</span>
                </td>
                <td class="px-5 py-2 text-xs text-gray-500 whitespace-nowrap">
                    {{ $discrepancy->created_at->format('Y-m-d') }}<br>by {{ $discrepancy->raisedBy->name ?? '—' }}
                </td>
                <td class="px-5 py-2 text-sm">
                    @if ($discrepancy->status === 'open')
                    <div class="flex flex-col gap-2 md:flex-row md:items-center">
                        <x-form.select wire:model.defer="resolutions.{{ $discrepancy->id }}" class="md:w-56">
                            <option value="">Choose resolution</option>
                            @foreach (\App\Models\ManualTracking\ManualDiscrepancy::RESOLUTIONS[$discrepancy->type] ?? [] as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </x-form.select>
                        <x-jet-input wire:model.defer="notes.{{ $discrepancy->id }}" type="text" placeholder="Notes" class="md:w-48" />
                        <button type="button" wire:click="resolve({{ $discrepancy->id }})"
                            class="btn-primary btn-sm">Resolve</button>
                    </div>
                    @else
                    <div class="text-gray-700 dark:text-gray-200">
                        {{ \App\Models\ManualTracking\ManualDiscrepancy::RESOLUTIONS[$discrepancy->type][$discrepancy->resolution] ?? $discrepancy->resolution }}
                    </div>
                    <div class="text-xs text-gray-500">
                        {{ optional($discrepancy->resolved_at)->format('Y-m-d') }} by {{ $discrepancy->resolvedBy->name ?? '—' }}
                        {{ $discrepancy->resolution_notes ? '· ' . $discrepancy->resolution_notes : '' }}
                    </div>
                    @endif
                </td>
            </x-data-table.tr>
            @empty
            <x-data-table.empty colspan="5" />
            @endforelse
        </x-slot>

        {{ $discrepancies->links() }}
    </x-form.table>
</div>
