<div class="space-y-5">
    @php
        $statusClasses = [
            'dispatched' => 'bg-amber-100 text-amber-800',
            'received' => 'bg-green-100 text-green-800',
            'received_with_discrepancy' => 'bg-red-100 text-red-800',
        ];
    @endphp

    @if (session('success'))
    <div class="p-3 text-sm text-green-800 bg-green-100 rounded-md">{{ session('success') }}</div>
    @endif

    <div class="p-5 bg-white rounded-lg shadow-sm dark:bg-gray-800">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <p class="text-xs text-gray-500 uppercase dark:text-gray-400">Shipment</p>
                <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ $shipment->reference }}</h3>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('manual-tracking.shipments.print', $shipment->id) }}" target="_blank"
                    class="text-sm font-medium text-blue-700 hover:underline dark:text-blue-300">Print delivery note</a>
                <span class="px-3 py-1 text-xs font-semibold rounded-full {{ $statusClasses[$shipment->status] ?? 'bg-gray-100 text-gray-700' }}">
                    {{ \App\Models\ManualTracking\ManualDistribution::STATUSES[$shipment->status] ?? $shipment->status }}
                </span>
            </div>
        </div>

        <dl class="grid grid-cols-1 gap-4 mt-4 text-sm md:grid-cols-4">
            <div>
                <dt class="text-gray-500 dark:text-gray-400">From</dt>
                <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $shipment->organization->name ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500 dark:text-gray-400">To</dt>
                <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $shipment->destinationOrganization->name ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500 dark:text-gray-400">Dispatched</dt>
                <dd class="text-gray-900 dark:text-gray-100">
                    {{ optional($shipment->distributed_at)->format('Y-m-d H:i') }}
                    <span class="text-gray-500">by {{ $shipment->dispatcher->name ?? '—' }}</span>
                </dd>
            </div>
            <div>
                <dt class="text-gray-500 dark:text-gray-400">Received</dt>
                <dd class="text-gray-900 dark:text-gray-100">
                    @if ($shipment->received_at)
                    {{ $shipment->received_at->format('Y-m-d H:i') }}
                    <span class="text-gray-500">by {{ $shipment->receiver->name ?? '—' }}</span>
                    @else
                    Expected {{ optional($shipment->expected_at)->format('Y-m-d') ?? '—' }}
                    @endif
                </dd>
            </div>
            <div>
                <dt class="text-gray-500 dark:text-gray-400">Carrier</dt>
                <dd class="text-gray-900 dark:text-gray-100">{{ $shipment->carrier ?: '—' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500 dark:text-gray-400">Waybill</dt>
                <dd class="text-gray-900 dark:text-gray-100">{{ $shipment->waybill_no ?: '—' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500 dark:text-gray-400">Vehicle / Driver</dt>
                <dd class="text-gray-900 dark:text-gray-100">
                    {{ $shipment->vehicle_no ?: '—' }} / {{ $shipment->driver_name ?: '—' }} {{ $shipment->driver_phone }}
                </dd>
            </div>
            <div>
                <dt class="text-gray-500 dark:text-gray-400">Remarks</dt>
                <dd class="text-gray-900 dark:text-gray-100">{{ $shipment->remarks ?: '—' }}</dd>
            </div>
        </dl>
        @if ($shipment->receipt_notes)
        <p class="mt-3 text-sm text-gray-700 dark:text-gray-300"><span class="text-gray-500">Receipt notes:</span> {{ $shipment->receipt_notes }}</p>
        @endif
    </div>

    <div class="overflow-x-auto bg-white rounded-lg shadow-sm dark:bg-gray-800">
        <table class="w-full text-sm text-left">
            <thead class="text-xs text-gray-600 bg-gray-50 dark:bg-gray-700 dark:text-gray-300">
                <tr>
                    <th class="px-4 py-2">Title</th>
                    <th class="px-4 py-2">Packages sent</th>
                    <th class="px-4 py-2">Sent (SB / TG)</th>
                    <th class="px-4 py-2">Received (SB / TG)</th>
                    <th class="px-4 py-2">Damaged (SB / TG)</th>
                    <th class="px-4 py-2">Discrepancies</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach ($shipment->lines as $line)
                <tr>
                    <td class="px-4 py-2 font-medium text-gray-900 dark:text-gray-100">{{ $line->book->title ?? '—' }}</td>
                    <td class="px-4 py-2 text-gray-700 dark:text-gray-300">
                        {{ $line->full_packages }}{{ $line->partial_package_books ? ' + partial (' . $line->partial_package_books . ' SB, ' . $line->partial_teacher_guides . ' TG)' : '' }}
                    </td>
                    <td class="px-4 py-2 text-gray-700 dark:text-gray-300">{{ number_format($line->quantity) }} / {{ number_format($line->teacher_guides) }}</td>
                    <td class="px-4 py-2 text-gray-700 dark:text-gray-300">
                        {{ $shipment->isInTransit() ? '—' : number_format((int) $line->received_quantity) . ' / ' . number_format((int) $line->received_teacher_guides) }}
                    </td>
                    <td class="px-4 py-2 text-gray-700 dark:text-gray-300">
                        {{ $shipment->isInTransit() ? '—' : $line->damaged_quantity . ' / ' . $line->damaged_teacher_guides }}
                    </td>
                    <td class="px-4 py-2 text-xs">
                        @forelse ($line->discrepancies as $discrepancy)
                        <div class="{{ $discrepancy->status === 'open' ? 'text-red-700' : 'text-gray-500' }}">
                            {{ ucfirst($discrepancy->type) }} {{ number_format($discrepancy->quantity) }}
                            {{ $discrepancy->edition === 'teacher' ? 'TG' : 'SB' }}
                            · {{ $discrepancy->status === 'open' ? 'open' : str_replace('_', ' ', $discrepancy->resolution) }}
                        </div>
                        @empty
                        <span class="text-gray-400">—</span>
                        @endforelse
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if ($canReceive)
    <div class="p-5 bg-white rounded-lg shadow-sm dark:bg-gray-800">
        <h3 class="text-base font-semibold text-gray-800 dark:text-gray-100">Scan packages as they are unloaded</h3>
        <p class="mb-3 text-xs text-gray-500 dark:text-gray-400">
            Optional. Each package's zone is checked against {{ $shipment->destinationOrganization->name }}; packages for another zone raise a misroute alert.
            Then use the scan counts to fill the receipt below.
        </p>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div>
                <x-jet-label for="scanTitleId" value="Title being unloaded" />
                <x-form.select wire:model="scanTitleId" id="scanTitleId">
                    <option value="">Not set</option>
                    @foreach ($shipment->lines as $line)
                    <option value="{{ $line->manual_book_id }}">{{ $line->book->title }}</option>
                    @endforeach
                </x-form.select>
            </div>
            <div class="md:col-span-2">
                <x-jet-label value="Packages scanned" />
                <ul class="mt-1 space-y-1 text-sm">
                    @foreach ($shipment->lines as $line)
                    @php
                        $expected = $line->full_packages + ($line->partial_package_books || $line->partial_teacher_guides ? 1 : 0);
                        $got = $scanCounts[$line->manual_book_id] ?? 0;
                    @endphp
                    <li class="{{ $got === $expected ? 'text-green-700' : 'text-gray-700 dark:text-gray-200' }}">
                        {{ $line->book->title }}: <span class="font-semibold">{{ $got }}</span> of {{ $expected }}
                    </li>
                    @endforeach
                    @if (! empty($scanCounts['']))
                    <li class="text-amber-600">{{ $scanCounts[''] }} scanned without a title</li>
                    @endif
                    @if ($scanAlerts)
                    <li class="font-semibold text-red-600">{{ $scanAlerts }} misroute {{ $scanAlerts === 1 ? 'alert' : 'alerts' }} raised</li>
                    @endif
                </ul>
            </div>
        </div>

        <x-scan.scanner class="mt-4" />

        @if ($lastScan)
        <x-scan.result :scan="$lastScan" class="mt-3" />
        @endif

        @if ($recentScans->isNotEmpty())
        <x-scan.log :scans="$recentScans" undo="voidScan" class="mt-3" />
        <div class="flex justify-end mt-3">
            <button type="button" wire:click="applyScans"
                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">Fill receipt from scans</button>
        </div>
        @endif
    </div>

    <form wire:submit.prevent="receive" class="p-5 bg-white border border-green-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-green-800">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h3 class="text-base font-semibold text-gray-800 dark:text-gray-100">Confirm receipt</h3>
            <x-shipment.entry-mode model="receiveMode" :value="$receiveMode" />
        </div>
        <p class="mb-4 text-xs text-gray-500 dark:text-gray-400">
            Count what physically arrived, by package or as total copies, whichever is easier. Any difference from what was sent, and any damaged copies, are recorded as discrepancies.
        </p>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-gray-600 dark:text-gray-300">
                    <tr>
                        <th class="px-2 py-2">Title</th>
                        @if ($receiveMode === 'totals')
                        <th class="px-2 py-2">Student books received</th>
                        <th class="px-2 py-2">Teacher guides received</th>
                        @else
                        <th class="px-2 py-2">Full packages</th>
                        <th class="px-2 py-2">Partial: student books</th>
                        <th class="px-2 py-2">Partial: teacher guides</th>
                        @endif
                        <th class="px-2 py-2">Damaged SB</th>
                        <th class="px-2 py-2">Damaged TG</th>
                        <th class="px-2 py-2">Counted vs sent</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $receiveFields = $receiveMode === 'totals'
                            ? ['student_books', 'teacher_guides', 'damaged_quantity', 'damaged_teacher_guides']
                            : ['full_packages', 'partial_package_books', 'partial_teacher_guides', 'damaged_quantity', 'damaged_teacher_guides'];
                    @endphp
                    @foreach ($shipment->lines as $line)
                    @php
                        $preview = $receivedPreview[$line->id];
                        $studentGap = $preview['student'] - $line->quantity;
                        $teacherGap = $preview['teacher'] - $line->teacher_guides;
                    @endphp
                    <tr wire:key="receive-{{ $line->id }}">
                        <td class="px-2 py-2 font-medium text-gray-900 dark:text-gray-100">{{ $line->book->title }}</td>
                        @foreach ($receiveFields as $field)
                        <td class="px-2 py-2">
                            <x-jet-input wire:model="counts.{{ $line->id }}.{{ $field }}" type="number" min="0" class="w-24" />
                        </td>
                        @endforeach
                        <td class="px-2 py-2 whitespace-nowrap {{ $studentGap || $teacherGap ? 'text-red-600 font-semibold' : 'text-green-700' }}">
                            {{ number_format($preview['student']) }} SB ({{ $studentGap >= 0 ? '+' : '' }}{{ $studentGap }})<br>
                            {{ number_format($preview['teacher']) }} TG ({{ $teacherGap >= 0 ? '+' : '' }}{{ $teacherGap }})
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            <x-jet-label for="receiptNotes" value="Notes" />
            <textarea wire:model.defer="receiptNotes" id="receiptNotes" rows="2"
                class="block w-full mt-1 border-gray-300 rounded-md dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100"></textarea>
        </div>

        <div class="flex justify-end mt-4">
            <button type="submit" wire:loading.attr="disabled"
                class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700 disabled:opacity-50">Confirm Receipt</button>
        </div>
    </form>
    @elseif ($shipment->isInTransit())
    <p class="text-sm text-gray-500 dark:text-gray-400">
        Waiting for {{ $shipment->destinationOrganization->name ?? 'the destination' }} to confirm receipt.
    </p>
    @endif
</div>
