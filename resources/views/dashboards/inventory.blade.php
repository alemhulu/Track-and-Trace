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
        $kpiIcon = fn ($label) => collect(['warehouse' => 'fi-rr-home-location-alt', 'location' => 'fi-rr-marker', 'low' => 'fi-rr-exclamation', 'guide' => 'fi-rr-book-alt', 'title' => 'fi-rr-book', 'book' => 'fi-rr-book', 'stock' => 'fi-rr-box', 'package' => 'fi-rr-box'])->first(fn ($icon, $word) => str_contains(strtolower($label), $word)) ?? 'fi-rr-chart-histogram';
    @endphp
    <x-slot name="header">
        <h2>{{ __('Inventory Dashboard') }}</h2>
        <p class="page-subtitle">Stock held across warehouses and locations in your scope.</p>
    </x-slot>

    <div class="pb-8">
        <div x-data="{ tab: 'manual' }"
            x-init="renderDashboardCharts(tab); $watch('tab', value => $nextTick(() => renderDashboardCharts(value)))">
            <x-dashboard.source-tabs class="mb-5" />

            @foreach ($sections as $key => $section)
            @php $sectionData = $inventoryData[$key]; @endphp
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
                        <h3 class="mb-4 card-title">{{ $section['stockTitle'] }}</h3>
                        <div id="{{ $key }}StockChart"></div>
                    </div>

                    <div class="p-5 card">
                        <h3 class="mb-4 card-title">Stock by Subject</h3>
                        <div id="{{ $key }}CompositionChart"></div>
                    </div>

                    <!-- Low Stock Alerts -->
                    <div class="p-5 card lg:col-span-2">
                        <h3 class="mb-4 card-title">
                            Low Stock Alerts (below {{ $inventoryData['lowStockThreshold'] }} copies)
                        </h3>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead class="text-xs font-semibold tracking-wider uppercase bg-slate-50 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400">
                                    <tr>
                                        <th class="px-4 py-2.5">Book</th>
                                        <th class="px-4 py-2.5">{{ $section['locationHeading'] }}</th>
                                        <th class="px-4 py-2.5">Current Balance</th>
                                        <th class="px-4 py-2.5">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                    @forelse($sectionData['lowStockAlerts'] as $alert)
                                    <tr>
                                        <td class="px-4 py-2.5 font-medium text-slate-900 dark:text-slate-100">{{ $alert['name'] }}</td>
                                        <td class="px-4 py-2.5 text-slate-600 dark:text-slate-300">{{ $alert['location'] }}</td>
                                        <td class="px-4 py-2.5 font-mono tabular-nums">{{ $alert['balance'] }}</td>
                                        <td class="px-4 py-2.5">
                                            <span class="badge-danger">Low</span>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-10 text-center text-slate-500 dark:text-slate-400">No low stock.</td>
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
        const data = @json($inventoryData);
        const palettes = {
            digital: ['#1e4ea2', '#4474cb', '#3c8ac0', '#9abbe8', '#16305d'],
            manual: ['#c67b03', '#f6ba12', '#cc4e3c', '#fbd647', '#9e5706'],
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
                chart: baseChart('bar'), grid: baseGrid(),
                plotOptions: { bar: { horizontal: key === 'manual' } },
                series: section.stockChart.series,
                xaxis: { categories: section.stockChart.categories },
                colors: [colors[0]],
                noData: { text: 'No stock recorded' },
            }).render();

            new ApexCharts(document.querySelector(`#${key}CompositionChart`), {
                chart: baseChart('donut'), dataLabels: { enabled: false }, plotOptions: { pie: { donut: { size: '68%' } } }, stroke: { colors: [isDark() ? '#0f172a' : '#fff'] },
                series: section.compositionChart.series,
                labels: section.compositionChart.labels,
                colors: colors,
                legend: { position: 'bottom' },
                noData: { text: 'No stock recorded' },
            }).render();
        }
    </script>
</x-app-layout>
