<div class="space-y-6">
    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-100">Manual Tracking Dashboard</h2>
            <p class="text-sm text-gray-500 dark:text-gray-300">Live stock movement, distribution activity, and book
                balance overview.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <x-jet-button wire:click="exportLocationSummaryCsv" type="button">Export Location CSV</x-jet-button>
            <x-jet-button wire:click="exportBookBalanceCsv" type="button">Export Book CSV</x-jet-button>
            <x-jet-button wire:click="exportUserActivityCsv" type="button">Export Activity CSV</x-jet-button>
            <x-jet-button wire:click="exportMovementHistoryCsv" type="button">Export Movement CSV</x-jet-button>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="p-4 bg-white border shadow-sm rounded-xl border-slate-200">
            <p class="text-sm text-slate-500">Books</p>
            <div class="flex items-end justify-between mt-2">
                <h3 class="text-3xl font-bold text-slate-900">{{ number_format($summary['total_books']) }}</h3>
                <span class="px-2 py-1 text-xs font-medium text-blue-700 bg-blue-100 rounded-full">Catalog</span>
            </div>
        </div>

        <div class="p-4 bg-white border shadow-sm rounded-xl border-slate-200">
            <p class="text-sm text-slate-500">Packages</p>
            <div class="flex items-end justify-between mt-2">
                <h3 class="text-3xl font-bold text-slate-900">{{ number_format($summary['total_packages']) }}</h3>
                <span class="px-2 py-1 text-xs font-medium rounded-full bg-violet-100 text-violet-700">Batches</span>
            </div>
        </div>

        <div class="p-4 bg-white border shadow-sm rounded-xl border-slate-200">
            <p class="text-sm text-slate-500">Distributed Qty</p>
            <div class="flex items-end justify-between mt-2">
                <h3 class="text-3xl font-bold text-slate-900">
                    {{ number_format($summary['total_quantity_distributed']) }}
                </h3>
                <span class="px-2 py-1 text-xs font-medium rounded-full bg-emerald-100 text-emerald-700">Moved</span>
            </div>
        </div>

        <div class="p-4 bg-white border shadow-sm rounded-xl border-slate-200">
            <p class="text-sm text-slate-500">Active Users</p>
            <div class="flex items-end justify-between mt-2">
                <h3 class="text-3xl font-bold text-slate-900">{{ number_format($summary['active_users']) }}</h3>
                <span class="px-2 py-1 text-xs font-medium rounded-full bg-amber-100 text-amber-700">Activity</span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
        <div class="p-4 bg-white border shadow-sm rounded-xl border-slate-200">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-lg font-semibold text-slate-800">Monthly Distribution Trend</h3>
                <span class="text-xs font-medium tracking-wide uppercase text-slate-400">Last 12 months</span>
            </div>
            <div id="manual-tracking-trend-chart" class="h-72"></div>
        </div>

        <div class="p-4 bg-white border shadow-sm rounded-xl border-slate-200">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-lg font-semibold text-slate-800">Top Distribution Regions</h3>
                <span class="text-xs font-medium tracking-wide uppercase text-slate-400">Volume</span>
            </div>
            <div id="manual-tracking-location-chart" class="h-72"></div>
        </div>
    </div>

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
                            <td class="py-2">{{ $row->region_name ?? '—' }}</td>
                            <td class="py-2">{{ $row->zone_name ?? '—' }}</td>
                            <td class="py-2">{{ $row->woreda_name ?? '—' }}</td>
                            <td class="py-2 font-semibold">{{ number_format($row->total_qty) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="py-3 text-gray-500">No data</td>
                        </tr>
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
                        <tr>
                            <td colspan="4" class="py-3 text-gray-500">No data</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="space-y-4">
        <div class="p-4 bg-white rounded-md shadow-sm dark:bg-gray-800">
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-semibold text-gray-700 dark:text-gray-100">Package Movement History</h3>
                <x-jet-button wire:click="exportMovementHistoryCsv" type="button">Export CSV</x-jet-button>
            </div>
            <div class="overflow-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500">
                            <th class="py-2">Book</th>
                            <th class="py-2">Source</th>
                            <th class="py-2">Destination</th>
                            <th class="py-2">Date</th>
                            <th class="py-2">Qty</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($movementHistory as $row)
                        <tr>
                            <td class="py-2">{{ $row->book_title ?? 'Unknown book' }}</td>
                            <td class="py-2">{{ $row->source_name ?? '—' }}</td>
                            <td class="py-2">{{ $row->destination_name ?? '—' }}</td>
                            <td class="py-2">
                                {{ $row->distributed_at ? \Carbon\Carbon::parse($row->distributed_at)->format('M d, Y') : '—' }}
                            </td>
                            <td class="py-2 font-semibold">{{ number_format($row->quantity) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-3 text-gray-500">No data</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
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
                            <td class="py-2">{{ $row->user_name ?? 'Unknown' }}</td>
                            <td class="py-2">{{ $row->action }}</td>
                            <td class="py-2 font-semibold">{{ number_format($row->total_actions) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="py-3 text-gray-500">No data</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof ApexCharts === 'undefined') {
            return;
        }

        const trendOptions = {
            chart: {
                type: 'bar',
                height: 280,
                toolbar: { show: false },
                background: 'transparent'
            },
            series: [{
                name: 'Distributed Qty',
                data: @json($distributionTrendSeries)
            }],
            xaxis: {
                categories: @json($distributionTrendLabels),
                labels: { style: { colors: '#64748b' } }
            },
            yaxis: {
                labels: {
                    formatter: (value) => Number(value).toLocaleString()
                }
            },
            plotOptions: {
                bar: {
                    borderRadius: 6,
                    columnWidth: '48%'
                }
            },
            colors: ['#2048a1'],
            fill: { opacity: 1 },
            dataLabels: { enabled: false },
            grid: { borderColor: '#e2e8f0' },
            tooltip: {
                y: { formatter: (value) => Number(value).toLocaleString() }
            }
        };

        const locationOptions = {
            chart: {
                type: 'donut',
                height: 280,
                toolbar: { show: false },
                background: 'transparent'
            },
            series: @json($locationChartSeries),
            labels: @json($locationChartLabels),
            colors: ['#2048a1', '#3b82f6', '#60a5fa', '#93c5fd', '#bfdbfe', '#dbeafe'],
            legend: {
                position: 'bottom',
                horizontalAlign: 'left'
            },
            dataLabels: {
                enabled: true,
                formatter: (val) => `${Number(val).toFixed(0)}%`
            },
            tooltip: {
                y: { formatter: (value) => Number(value).toLocaleString() }
            }
        };

        new ApexCharts(document.querySelector('#manual-tracking-trend-chart'), trendOptions).render();
        new ApexCharts(document.querySelector('#manual-tracking-location-chart'), locationOptions).render();
    });
</script>
