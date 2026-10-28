<x-app-layout>
    @php
        $sections = [
            'manual' => [
                'title' => 'Manual Tracking',
                'subtitle' => 'Supplementary books handed out and recorded by hand',
                'regionalTitle' => 'Copies by Destination Region',
                'trendsTitle' => 'Distribution Trends',
                'recentTitle' => 'Recent Manual Distributions',
                'columns' => ['Book', 'From → To', 'Quantity'],
            ],
            'digital' => [
                'title' => 'Digital Tracking',
                'subtitle' => 'Barcoded packages moving between warehouses within your scope',
                'regionalTitle' => 'Regional Stock Allocation',
                'trendsTitle' => 'Delivery Trends',
                'recentTitle' => 'Recent Distribution Hubs',
                'columns' => ['Warehouse', 'Region', 'Status'],
            ],
        ];
    @endphp
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Distribution Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8" x-data="{ tab: 'manual' }"
            x-init="renderDashboardCharts(tab); $watch('tab', value => $nextTick(() => renderDashboardCharts(value)))">
            <x-dashboard.source-tabs class="mb-6" />

            @foreach ($sections as $key => $section)
            @php $sectionData = $distributionData[$key]; @endphp
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
                        <h3 class="text-lg font-semibold mb-4 text-gray-800 dark:text-gray-200">{{ $section['regionalTitle'] }}</h3>
                        <div id="{{ $key }}RegionalChart"></div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 p-6 shadow-sm rounded-lg">
                        <h3 class="text-lg font-semibold mb-4 text-gray-800 dark:text-gray-200">
                            {{ $section['trendsTitle'] }} ({{ $distributionData['trendDays'] }} Days)
                        </h3>
                        <div id="{{ $key }}TrendsChart"></div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 p-6 shadow-sm rounded-lg lg:col-span-2">
                        <h3 class="text-lg font-semibold mb-4 text-gray-800 dark:text-gray-200">{{ $section['recentTitle'] }}</h3>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead class="bg-gray-50 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                                    <tr>
                                        @foreach ($section['columns'] as $column)
                                        <th class="px-4 py-2">{{ $column }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @forelse($sectionData['recent'] as $row)
                                    <tr>
                                        <td class="px-4 py-2 text-gray-900 dark:text-gray-100">{{ $row['name'] }}</td>
                                        <td class="px-4 py-2 text-gray-600 dark:text-gray-300">{{ $row['detail'] }}</td>
                                        <td class="px-4 py-2">
                                            <span class="px-2 py-1 text-xs rounded-full {{ $key === 'digital' ? 'bg-green-100 text-green-700' : 'bg-teal-100 text-teal-700' }}">{{ $row['status'] }}</span>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="3" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">Nothing recorded yet.</td>
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
        const data = @json($distributionData);
        const colors = { digital: '#2048a1', manual: '#0f766e' };

        const renderedDashboardTabs = new Set();

        // Charts are drawn the first time their tab is shown; ApexCharts cannot size a hidden container
        function renderDashboardCharts(key) {
            if (renderedDashboardTabs.has(key)) {
                return;
            }
            renderedDashboardTabs.add(key);

            const color = colors[key];
            const section = data[key];

            new ApexCharts(document.querySelector(`#${key}RegionalChart`), {
                chart: { type: 'bar', height: 350 },
                series: section.regionalChart.series,
                xaxis: { categories: section.regionalChart.categories },
                colors: [color],
                noData: { text: 'No distributions recorded' },
            }).render();

            new ApexCharts(document.querySelector(`#${key}TrendsChart`), {
                chart: { type: 'line', height: 350 },
                series: section.trendsChart.series,
                xaxis: { categories: section.trendsChart.categories, type: 'datetime' },
                colors: [color],
                stroke: { curve: 'smooth' },
            }).render();
        }
    </script>
</x-app-layout>
