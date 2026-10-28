<x-app-layout>
    @php
        $scopeLevel = $dashboardData['scopeLevel'] ?? 'user';
        $kpiGroups = $dashboardData['kpiGroups'] ?? [];
        $kpiIcons = [
            'Print Orders' => 'fi-rr-print',
            'Distributed Books' => 'fi-rr-truck-side',
            'Stock on Hand' => 'fi-rr-box',
            'Warehouses' => 'fi-rr-home-location-alt',
            'Organizations' => 'fi-rr-building',
            'Distribution Records' => 'fi-rr-document',
        ];
        $kpiTones = ['indigo' => 'brand', 'blue' => 'sky', 'emerald' => 'emerald', 'amber' => 'gold', 'teal' => 'emerald', 'slate' => 'slate'];
    @endphp
    <x-slot name="header">
        <div class="flex flex-wrap items-center gap-3">
            <div>
                <h2>{{ __('Dashboard') }}</h2>
                <p class="page-subtitle">Textbook supply at a glance, from print orders to students' hands.</p>
            </div>
            <span class="badge-gold">
                <i class="flex fi fi-rr-marker"></i>
                {{ $dashboardData['scopeLabel'] ?? 'User Scope' }}
            </span>
        </div>
    </x-slot>

    <div class="pb-8" x-data="{ tab: 'manual' }"
        x-init="renderDashboardCharts(tab); $watch('tab', value => $nextTick(() => renderDashboardCharts(value)))">
        <x-dashboard.source-tabs class="mb-5" />

        @foreach (['manual', 'digital'] as $source)
        <div id="panel-{{ $source }}" role="tabpanel" aria-labelledby="tab-{{ $source }}" class="space-y-6"
            x-show="tab === '{{ $source }}'" @if ($source !== 'manual') style="display: none" @endif>
            <section>
                <p class="mb-3 text-sm text-slate-500 dark:text-slate-400">
                    {{ $kpiGroups[$source]['subtitle'] }}
                </p>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach ($kpiGroups[$source]['cards'] as $card)
                    <x-kpi-card :label="$card['label'] ?? 'KPI'" :value="number_format((int) ($card['value'] ?? 0))"
                        :hint="$card['hint'] ?? ''" :icon="$kpiIcons[$card['label'] ?? ''] ?? 'fi-rr-chart-histogram'"
                        :tone="$kpiTones[$card['style'] ?? 'slate'] ?? 'brand'"
                        :delta="$card['delta'] ?? ['label' => '0.0% vs previous 30 days', 'direction' => 'flat']" />
                    @endforeach
                </div>
            </section>

            @if ($source === 'manual' && ! empty($dashboardData['oversight']))
            <x-dashboard.coverage :overview="$dashboardData['oversight']" :year="$dashboardData['academicYear']" />
            @endif

            <section class="grid grid-cols-6 gap-4">
                <div class="col-span-6 p-4 card lg:col-span-4" id="{{ $source }}SubjectChart"></div>
                <div class="col-span-6 p-4 card lg:col-span-2" id="{{ $source }}StatusChart"></div>
                <div class="col-span-6 p-4 card md:col-span-3" id="{{ $source }}SummaryChart"></div>
                <div class="col-span-6 p-4 card md:col-span-3" id="{{ $source }}NetworkChart"></div>
            </section>
        </div>
        @endforeach
    </div>
</x-app-layout>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    const dashboardData = @json($dashboardData);
    const renderedDashboardTabs = new Set();

    // Brand palette from the MoE logo
    const brandBlues = ['#1e4ea2', '#4474cb', '#9abbe8', '#16305d', '#3c8ac0'];
    const isDark = () => document.body.classList.contains('dark');
    const baseChart = () => ({
        fontFamily: 'Nunito, sans-serif',
        toolbar: { show: false },
        foreColor: isDark() ? '#94a3b8' : '#64748b',
        background: 'transparent',
    });
    const baseGrid = () => ({ borderColor: isDark() ? '#1e293b' : '#e2e8f0', strokeDashArray: 4 });

    const donutTitle = (chartData) => ({
        title: {
            text: chartData.title,
            offsetX: 0,
            style: { fontSize: '16px', fontWeight: 600, color: isDark() ? '#f1f5f9' : '#0f172a', fontFamily: 'Nunito' }
        },
        subtitle: {
            text: chartData.subtitle,
            offsetX: 1,
            style: { fontSize: '13px', color: '#64748b', fontFamily: 'Nunito' }
        },
    });

    const donutOptions = (chartData, colors) => ({
        ...donutTitle(chartData),
        colors: colors,
        series: chartData.series,
        labels: chartData.labels,
        chart: { ...baseChart(), type: 'donut', height: 380 },
        stroke: { colors: [isDark() ? '#0f172a' : '#ffffff'] },
        plotOptions: { pie: { donut: { size: '68%', labels: { show: true, total: { show: true, label: 'Total' } } } } },
        dataLabels: { enabled: false },
        legend: { position: 'bottom', offsetY: 0, markers: { radius: 12 } },
        responsive: [{ breakpoint: 480, options: { chart: { width: 200 }, legend: { position: 'bottom' } } }]
    });

    const chartFactories = {
        Subject: (chartData) => ({
            colors: ['#1e4ea2', '#f6ba12', '#3c8ac0'],
            series: chartData.series,
            chart: { ...baseChart(), type: 'bar', height: 350 },
            grid: baseGrid(),
            legend: { position: 'top', horizontalAlign: 'left', markers: { radius: 12 } },
            plotOptions: { bar: { horizontal: false, borderRadius: 4, columnWidth: '60%' } },
            dataLabels: { enabled: false },
            stroke: { show: true, width: 1, radius: 5, colors: ['transparent'] },
            xaxis: { categories: chartData.categories },
            fill: { opacity: 1 },
            tooltip: { shared: true, followCursor: true, intersect: false },
        }),
        Status: (chartData) => ({
            colors: [...brandBlues, '#10b981'],
            series: chartData.series,
            chart: { ...baseChart(), height: 350, type: 'polarArea' },
            title: { text: chartData.title, align: 'left', style: { fontSize: '16px', fontWeight: 600, fontFamily: 'Nunito', color: isDark() ? '#f1f5f9' : '#0f172a' } },
            labels: chartData.labels,
            fill: { opacity: 1 },
            stroke: { width: 1, colors: undefined },
            yaxis: { show: false },
            legend: { position: 'bottom' },
            plotOptions: { polarArea: { rings: { strokeWidth: 0 }, spokes: { strokeWidth: 0 } } },
            theme: { monochrome: { enabled: true, color: '#1e4ea2', shadeTo: 'light', shadeIntensity: 0.65 } }
        }),
        Summary: (chartData) => donutOptions(chartData, ['#1e4ea2', '#4474cb', '#9abbe8', '#10b981']),
        Network: (chartData) => donutOptions(chartData, ['#f6ba12', '#cc4e3c', '#c67b03', '#10b981']),
    };

    // Charts are drawn the first time their tab is shown; ApexCharts cannot size a hidden container
    function renderDashboardCharts(source) {
        if (renderedDashboardTabs.has(source)) {
            return;
        }
        renderedDashboardTabs.add(source);

        const charts = dashboardData.charts[source];
        Object.entries(chartFactories).forEach(([name, factory]) => {
            const chartData = charts[name.toLowerCase()];
            new ApexCharts(document.querySelector(`#${source}${name}Chart`), factory(chartData)).render();
        });
    }

</script>
