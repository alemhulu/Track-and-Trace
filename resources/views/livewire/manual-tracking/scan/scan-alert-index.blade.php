<div class="space-y-4">
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h3 class="section-title">Misrouted packages</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">Raised when a package or book is scanned somewhere it does not belong.</p>
        </div>
        <div class="w-48">
            <x-form.select wire:model="statusFilter">
                <option value="open">Open</option>
                <option value="resolved">Closed</option>
                <option value="">All</option>
            </x-form.select>
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
                        {{ $alert->organization->name ?? '—' }}
                        <div class="text-xs text-gray-500">{{ $alert->context === 'receive' ? 'Receiving' : 'Sorting' }}</div>
                    </td>
                    <td class="px-4 py-2">
                        <div class="font-semibold text-red-700 dark:text-red-400">{{ \App\Models\ManualTracking\ManualScan::FLAG_LABELS[$alert->flag] ?? $alert->flag }}</div>
                        <div class="text-gray-700 dark:text-gray-200">{{ $alert->flag_message }}</div>
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
                        @if ($alert->alert_status === 'open')
                        <div class="flex gap-2">
                            <x-jet-input wire:model.defer="notes.{{ $alert->id }}" type="text" placeholder="What was done, e.g. sent on to Arsi Zone" class="flex-1 text-sm" />
                            <button type="button" wire:click="resolve({{ $alert->id }})"
                                class="btn-primary btn-sm">Close</button>
                        </div>
                        @else
                        <div class="text-gray-700 dark:text-gray-200">{{ $alert->resolution_note }}</div>
                        <div class="text-xs text-gray-500">{{ $alert->resolver->name ?? '—' }}, {{ optional($alert->resolved_at)->format('Y-m-d') }}</div>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-4 py-6 text-center text-gray-500">No misroute alerts.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $alerts->links() }}
</div>
