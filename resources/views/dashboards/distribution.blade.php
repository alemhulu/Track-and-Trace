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
        $kpiIcon = fn ($label) => collect(['region' => 'fi-rr-map-marker-home', 'trend' => 'fi-rr-chart-histogram', 'transit' => 'fi-rr-truck-side', 'deliver' => 'fi-rr-shield-check', 'shipment' => 'fi-rr-truck-side', 'cop' => 'fi-rr-book', 'book' => 'fi-rr-book', 'hub' => 'fi-rr-home-location-alt', 'warehouse' => 'fi-rr-home-location-alt'])->first(fn ($icon, $word) => str_contains(strtolower($label), $word)) ?? 'fi-rr-chart-histogram';
    @endphp
    <x-slot name="header">
        <h2>{{ __('Distribution Dashboard') }}</h2>
        <p class="page-subtitle">How books are moving between hubs, regions and schools.</p>
    </x-slot>

    <div class="pb-8">
        <div x-data="{ tab: 'manual' }"
            x-init="renderDashboardCharts(tab); $watch('tab', value => $nextTick(() => renderDashboardCharts(value)))">
            <x-dashboard.source-tabs class="mb-5" />

            @foreach ($sections as $key => $section)
            @php $sectionData = $distributionData[$key]; @endphp
            <section id="panel-{{ $key }}" role="tabpanel" aria-labelledby="tab-{{ $key }}"
                x-show="tab === '{{ $key }}'" @if ($key !== 'manual') style="display: none" @endif>
                <p class="mb-3 text-sm text-slate-500 dark:text-slate-400">{{ $section['subtitle'] }}</p>

                <!-- KPI Cards -->
                <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-3">
                    @foreach($sectionData['kpiCards'] as $card)
                    <x-kpi-card :label="$card['label']" :value="number_format($card['value'])" :hint="$card['hint']"
                        :tone="$card['style']" :icon="$kpiIcon($card['label'])" />
                    @endforeach
                </div>

                <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                    <div class="p-5 card">
                        <h3 class="mb-4 card-title">{{ $section['regionalTitle'] }}</h3>
                        <div id="{{ $key }}RegionalChart"></div>
                    </div>

                    <div class="p-5 card">
                        <h3 class="mb-4 card-title">
                            {{ $section['trendsTitle'] }} ({{ $distributionData['trendDays'] }} Days)
                        </h3>
                        <div id="{{ $key }}TrendsChart"></div>
                    </div>

                    <div class="p-5 card lg:col-span-2">
                        <h3 class="mb-4 card-title">{{ $section['recentTitle'] }}</h3>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead class="text-xs font-semibold tracking-wider uppercase bg-slate-50 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400">
                                    <tr>
                                        @foreach ($section['columns'] as $column)
                                        <th class="px-4 py-2.5">{{ $column }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                    @forelse($sectionData['recent'] as $row)
                                    <tr>
                                        <td class="px-4 py-2.5 font-medium text-slate-900 dark:text-slate-100">{{ $row['name'] }}</td>
                                        <td class="px-4 py-2.5 text-slate-600 dark:text-slate-300">{{ $row['detail'] }}</td>
                                        <td class="px-4 py-2.5">
                                            <span class="{{ $key === 'digital' ? 'badge-success' : 'badge-brand' }}">{{ $row['status'] }}</span>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="3" class="px-4 py-10 text-center text-slate-500 dark:text-slate-400">Nothing recorded yet.</td>
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
        const isDark = () => document.body.classList.contains('dark');
        const baseChart = (type) => ({ type, height: 350, fontFamily: 'Nunito, sans-serif', toolbar: { show: false }, background: 'transparent', foreColor: isDark() ? '#94a3b8' : '#64748b' });
        const baseGrid = () => ({ borderColor: isDark() ? '#1e293b' : '#e2e8f0', strokeDashArray: 4 });
    </script>
    <script>
        const data = @json($distributionData);
        const colors = { digital: '#1e4ea2', manual: '#c67b03' };

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
                chart: baseChart('bar'), grid: baseGrid(),
                series: section.regionalChart.series,
                xaxis: { categories: section.regionalChart.categories },
                colors: [color],
                noData: { text: 'No distributions recorded' },
            }).render();

            new ApexCharts(document.querySelector(`#${key}TrendsChart`), {
                chart: baseChart('area'), grid: baseGrid(), fill: { type: 'gradient', gradient: { opacityFrom: 0.35, opacityTo: 0.02 } }, dataLabels: { enabled: false },
                series: section.trendsChart.series,
                xaxis: { categories: section.trendsChart.categories, type: 'datetime' },
                colors: [color],
                stroke: { curve: 'smooth' },
            }).render();
        }
    </script>
</x-app-layout>
