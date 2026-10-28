<x-app-layout>
    @php
        $sections = [
            'manual' => [
                'title' => 'Manual Tracking',
                'subtitle' => 'Books tracked by quantity, held by locations within your scope',
                'stockTitle' => 'Student Books by Title',
                'locationHeading' => 'Location',
            ],
            'digital' => [
                'title' => 'Digital Tracking',
                'subtitle' => 'Barcoded packages held in warehouses within your scope',
                'stockTitle' => 'Stock Level by Warehouse',
                'locationHeading' => 'Warehouse',
            ],
        ];
    @endphp
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Inventory Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8" x-data="{ tab: 'manual' }"
            x-init="renderDashboardCharts(tab); $watch('tab', value => $nextTick(() => renderDashboardCharts(value)))">
            <x-dashboard.source-tabs class="mb-6" />

            @foreach ($sections as $key => $section)
            @php $sectionData = $inventoryData[$key]; @endphp
            <section id="panel-{{ $key }}" role="tabpanel" aria-labelledby="tab-{{ $key }}"
                x-show="tab === '{{ $key }}'" @if ($key !== 'manual') style="display: none" @endif>
                <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">{{ $section['subtitle'] }}</p>

                <!-- KPI Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    @foreach($sectionData['kpiCards'] as $card)
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $card['label'] }}</div>
                        <div class="text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($card['value']) }}</div>
                        <div class="text-xs text-gray-400 mt-1">{{ $card['hint'] }}</div>
                        <div class="mt-2 w-2 h-2 rounded-full bg-{{ $card['style'] }}-500"></div>
                    </div>
                    @endforeach
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <div class="bg-white dark:bg-gray-800 p-6 shadow-sm rounded-lg">
                        <h3 class="text-lg font-semibold mb-4 text-gray-800 dark:text-gray-200">{{ $section['stockTitle'] }}</h3>
                        <div id="{{ $key }}StockChart"></div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 p-6 shadow-sm rounded-lg">
                        <h3 class="text-lg font-semibold mb-4 text-gray-800 dark:text-gray-200">Stock by Subject</h3>
                        <div id="{{ $key }}CompositionChart"></div>
                    </div>

                    <!-- Low Stock Alerts -->
                    <div class="bg-white dark:bg-gray-800 p-6 shadow-sm rounded-lg lg:col-span-2">
                        <h3 class="text-lg font-semibold mb-4 text-gray-800 dark:text-gray-200">
                            Low Stock Alerts (below {{ $inventoryData['lowStockThreshold'] }} copies)
                        </h3>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead class="bg-gray-50 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                                    <tr>
                                        <th class="px-4 py-2">Book</th>
                                        <th class="px-4 py-2">{{ $section['locationHeading'] }}</th>
                                        <th class="px-4 py-2">Current Balance</th>
                                        <th class="px-4 py-2">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @forelse($sectionData['lowStockAlerts'] as $alert)
                                    <tr>
                                        <td class="px-4 py-2 text-gray-900 dark:text-gray-100">{{ $alert['name'] }}</td>
                                        <td class="px-4 py-2 text-gray-600 dark:text-gray-300">{{ $alert['location'] }}</td>
                                        <td class="px-4 py-2 font-mono">{{ $alert['balance'] }}</td>
                                        <td class="px-4 py-2">
                                            <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-700">Low</span>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">No low stock.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>
            @endforeach
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        const data = @json($inventoryData);
        const palettes = {
            digital: ['#2048a1', '#3b82f6', '#60a5fa', '#93c5fd', '#bfdbfe'],
            manual: ['#0f766e', '#14b8a6', '#2dd4bf', '#5eead4', '#99f6e4'],
        };

        const renderedDashboardTabs = new Set();

        // Charts are drawn the first time their tab is shown; ApexCharts cannot size a hidden container
        function renderDashboardCharts(key) {
            if (renderedDashboardTabs.has(key)) {
                return;
            }
            renderedDashboardTabs.add(key);

            const colors = palettes[key];
            const section = data[key];

            new ApexCharts(document.querySelector(`#${key}StockChart`), {
                chart: { type: 'bar', height: 350 },
                plotOptions: { bar: { horizontal: key === 'manual' } },
                series: section.stockChart.series,
                xaxis: { categories: section.stockChart.categories },
                colors: [colors[0]],
                noData: { text: 'No stock recorded' },
            }).render();

            new ApexCharts(document.querySelector(`#${key}CompositionChart`), {
                chart: { type: 'donut', height: 350 },
                series: section.compositionChart.series,
                labels: section.compositionChart.labels,
                colors: colors,
                legend: { position: 'bottom' },
                noData: { text: 'No stock recorded' },
            }).render();
        }
    </script>
</x-app-layout>
