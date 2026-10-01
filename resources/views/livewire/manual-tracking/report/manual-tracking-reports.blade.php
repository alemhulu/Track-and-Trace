<div class="space-y-6">
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <div class="p-4 bg-white rounded-md shadow-sm dark:bg-gray-800">
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-semibold text-gray-700 dark:text-gray-100">Location-wise Distribution Summary</h3>
                <x-jet-button wire:click="exportLocationSummaryCsv" type="button">Export CSV</x-jet-button>
            </div>
            <div class="overflow-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500">
                            <th class="py-2">Region</th>
                            <th class="py-2">Zone</th>
                            <th class="py-2">Woreda</th>
                            <th class="py-2">Qty</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($locationSummary as $row)
                        <tr>
                            <td class="py-2">{{ $row->region_id }}</td>
                            <td class="py-2">{{ $row->zone_id }}</td>
                            <td class="py-2">{{ $row->woreda_id }}</td>
                            <td class="py-2 font-semibold">{{ number_format($row->total_qty) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="py-3 text-gray-500">No data</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="p-4 bg-white rounded-md shadow-sm dark:bg-gray-800">
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-semibold text-gray-700 dark:text-gray-100">Book Stock In/Out Balance</h3>
                <x-jet-button wire:click="exportBookBalanceCsv" type="button">Export CSV</x-jet-button>
            </div>
            <div class="overflow-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500">
                            <th class="py-2">Book</th>
                            <th class="py-2">In</th>
                            <th class="py-2">Out</th>
                            <th class="py-2">Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bookBalance as $row)
                        <tr>
                            <td class="py-2">{{ $row->title }}</td>
                            <td class="py-2">{{ number_format($row->stock_in) }}</td>
                            <td class="py-2">{{ number_format($row->stock_out) }}</td>
                            <td class="py-2 font-semibold">{{ number_format($row->latest_balance) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="py-3 text-gray-500">No data</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <div class="p-4 bg-white rounded-md shadow-sm dark:bg-gray-800">
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-semibold text-gray-700 dark:text-gray-100">User Activity Audit</h3>
                <x-jet-button wire:click="exportUserActivityCsv" type="button">Export CSV</x-jet-button>
            </div>
            <div class="overflow-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500">
                            <th class="py-2">User</th>
                            <th class="py-2">Action</th>
                            <th class="py-2">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($userActivity as $row)
                        <tr>
                            <td class="py-2">{{ $row->user_id ?? '-' }}</td>
                            <td class="py-2">{{ $row->action }}</td>
                            <td class="py-2 font-semibold">{{ number_format($row->total_actions) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="py-3 text-gray-500">No data</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="p-4 bg-white rounded-md shadow-sm dark:bg-gray-800">
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-semibold text-gray-700 dark:text-gray-100">Package Movement History</h3>
                <x-jet-button wire:click="exportMovementHistoryCsv" type="button">Export CSV</x-jet-button>
            </div>
            <div class="overflow-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500">
                            <th class="py-2">Reference</th>
                            <th class="py-2">Date</th>
                            <th class="py-2">Qty</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($movementHistory as $row)
                        <tr>
                            <td class="py-2">{{ $row->reference }}</td>
                            <td class="py-2">{{ $row->distributed_at }}</td>
                            <td class="py-2 font-semibold">{{ number_format($row->quantity) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="py-3 text-gray-500">No data</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
