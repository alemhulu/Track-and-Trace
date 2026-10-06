<x-app-layout>
    @php
        $scopeLevel = $dashboardData['scopeLevel'] ?? 'user';
        $kpiCards = $dashboardData['kpiCards'] ?? [];
        $scopeBadgeClasses = match ($scopeLevel) {
            'national' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-100',
            'region' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-100',
            'zone' => 'bg-teal-100 text-teal-800 dark:bg-teal-900 dark:text-teal-100',
            'woreda' => 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-100',
            'organization' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-100',
            'none' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-100',
            default => 'bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-100',
        };
    @endphp
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                {{ __('Dashboard') }}
            </h2>
            <span class="inline-flex items-center px-3 py-1 text-xs font-semibold tracking-wide uppercase rounded-full {{ $scopeBadgeClasses }}">
                {{ $dashboardData['scopeLabel'] ?? 'User Scope' }}
            </span>
        </div>
    </x-slot>

    {{-- <div class="grid grid-cols-3 gap-5 p-5 my-5 bg-white">
    {!! DNS2D::getBarcodeSVG("G-9 Biology: 0000010000001", 'QRCODE')!!}

  </div> --}}

    <div class="mb-8">
        <div class="w-auto">
            <div class="overflow-hidden shadow-xl sm:rounded-lg dark:bg-gray-800">
                <section class="p-5">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">
                        @foreach ($kpiCards as $card)
                            @php
                                $cardStyle = $card['style'] ?? 'slate';
                                $delta = $card['delta'] ?? ['label' => '0.0% vs previous 30 days', 'direction' => 'flat'];
                                $cardClasses = match ($cardStyle) {
                                    'indigo' => 'border-indigo-200 bg-indigo-50 dark:border-indigo-700 dark:bg-indigo-900/30',
                                    'blue' => 'border-blue-200 bg-blue-50 dark:border-blue-700 dark:bg-blue-900/30',
                                    'emerald' => 'border-emerald-200 bg-emerald-50 dark:border-emerald-700 dark:bg-emerald-900/30',
                                    'amber' => 'border-amber-200 bg-amber-50 dark:border-amber-700 dark:bg-amber-900/30',
                                    'teal' => 'border-teal-200 bg-teal-50 dark:border-teal-700 dark:bg-teal-900/30',
                                    default => 'border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-900/30',
                                };
                                $deltaClasses = match ($delta['direction'] ?? 'flat') {
                                    'up' => 'text-emerald-700 dark:text-emerald-300',
                                    'down' => 'text-rose-700 dark:text-rose-300',
                                    default => 'text-gray-500 dark:text-gray-400',
                                };
                                $deltaIcon = match ($delta['direction'] ?? 'flat') {
                                    'up' => '▲',
                                    'down' => '▼',
                                    default => '•',
                                };
                            @endphp
                            <div class="p-4 border rounded-lg {{ $cardClasses }}">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-300">
                                    {{ $card['label'] ?? 'KPI' }}
                                </p>
                                <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">
                                    {{ number_format((int) ($card['value'] ?? 0)) }}
                                </p>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    {{ $card['hint'] ?? '' }}
                                </p>
                                <p class="mt-2 flex items-center gap-1 text-xs font-semibold {{ $deltaClasses }}">
                                    <span>{{ $deltaIcon }}</span>
                                    <span>{{ $delta['label'] ?? '0.0% vs previous 30 days' }}</span>
                                </p>
                            </div>
                        @endforeach
                    </div>
                </section>

                <section>
                    <div class="grid grid-cols-6 gap-5 rounded-lg ">
                        <div class="col-span-6 p-4 bg-white rounded-lg shadow-md lg:col-span-4 dark:bg-gray-900"
                            id="chart">
                        </div>
                        <div class="col-span-6 p-4 bg-white rounded-lg shadow-md lg:col-span-2 dark:bg-gray-900"
                            id="chart3">
                        </div>
                        <div class="col-span-6 p-4 bg-white rounded-lg shadow-md md:col-span-3 dark:bg-gray-900"
                            id="chart1">
                        </div>
                        <div class="col-span-6 p-4 bg-white rounded-lg shadow-md md:col-span-3 dark:bg-gray-900"
                            id="chart2">
                        </div>
                    </div>
                </section>

                {{-- <section class="rounded-lg">
                    <div class="px-4 py-5 mx-auto mb-3 sm:px-6 lg:px-8">
                        <div class="mt-4">
                            <p class="text-xl font-bold text-gray-900 sm:text-2xl dark:text-gray-400">Print Orders
                            </p>
                            <dl class="grid grid-cols-1 gap-5 sm:grid-cols-4">
                                <div class="flex flex-col px-4 py-8 text-center border border-gray-200 rounded-lg">
                                    <dt class="order-last text-lg font-medium text-gray-500 dark:text-gray-400">
                                        Total Orders
                                    </dt>

                                    <dd class="text-4xl font-extrabold text-gray-600 dark:text-gray-200 md:text-5xl">
                                        70
                                    </dd>
                                </div>

                                <div class="flex flex-col px-4 py-8 text-center border border-green-200 rounded-lg">
                                    <dt class="order-last text-lg font-medium text-gray-500 dark:text-gray-400">
                                        Total Accepted
                                    </dt>

                                    <dd class="text-4xl font-extrabold text-green-600 md:text-5xl">
                                        67
                                    </dd>
                                </div>

                                <div class="flex flex-col px-4 py-8 text-center border border-red-200 rounded-lg">
                                    <dt class="order-last text-lg font-medium text-gray-500 dark:text-gray-400">
                                        Total Rejected
                                    </dt>

                                    <dd class="text-4xl font-extrabold text-red-600 md:text-5xl">5</dd>
                                </div>

                                <div class="flex flex-col px-4 py-8 text-center border border-green-200 rounded-lg">
                                    <dt class="order-last text-lg font-medium text-gray-500 dark:text-gray-400">
                                        Total Printed
                                    </dt>

                                    <dd class="text-4xl font-extrabold text-green-600 md:text-5xl">62</dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </section>

                <section class="rounded-lg">
                    <div class="px-4 py-5 mx-auto mb-3 sm:px-6 lg:px-8">
                        <div class="mt-4">
                            <p class="text-xl font-bold text-gray-900 dark:text-gray-400 sm:text-2xl">Books
                            </p>
                            <dl class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                                <div class="flex flex-col px-4 py-8 text-center border border-gray-200 rounded-lg">
                                    <dt class="order-last text-lg font-medium text-gray-500 dark:text-gray-400">
                                        Total In Stock
                                    </dt>

                                    <dd class="text-4xl font-extrabold text-gray-600 md:text-5xl dark:text-gray-200">
                                        2.1M
                                    </dd>
                                </div>

                                <div class="flex flex-col px-4 py-8 text-center border border-yellow-200 rounded-lg">
                                    <dt class="order-last text-lg font-medium text-gray-500 dark:text-gray-400">
                                        Total Distributed
                                    </dt>

                                    <dd class="text-4xl font-extrabold text-yellow-600 md:text-5xl">2.4M</dd>
                                </div>

                                <div class="flex flex-col px-4 py-8 text-center border border-green-200 rounded-lg">
                                    <dt class="order-last text-lg font-medium text-gray-500 dark:text-gray-400">
                                        Total on Students Hand
                                    </dt>

                                    <dd class="text-4xl font-extrabold text-yellow-600 md:text-5xl">2.1M</dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </section>

                <section class="rounded-lg">
                    <div class="px-4 py-5 mx-auto mb-3 sm:px-6 lg:px-8">
                        <div class="mt-4">
                            <p class="text-xl font-bold text-gray-900 sm:text-2xl dark:text-gray-400">Wearhouses
                            </p>
                            <dl class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                                <div class="flex flex-col px-4 py-8 text-center border border-gray-200 rounded-lg">
                                    <dt class="order-last text-lg font-medium text-gray-500 dark:text-gray-400">
                                        Total Wearhouses
                                    </dt>

                                    <dd class="text-4xl font-extrabold text-gray-600 md:text-5xl dark:text-gray-200">
                                        700
                                    </dd>
                                </div>

                                <div class="flex flex-col px-4 py-8 text-center border border-blue-200 rounded-lg">
                                    <dt class="order-last text-lg font-medium text-gray-500 dark:text-gray-400">
                                        Total Stores
                                    </dt>

                                    <dd class="text-4xl font-extrabold text-blue-600 md:text-5xl">2.4K</dd>
                                </div>

                                <div class="flex flex-col px-4 py-8 text-center border border-yellow-200 rounded-lg">
                                    <dt class="order-last text-lg font-medium text-gray-500 dark:text-gray-400">
                                        Total Books in Stores
                                    </dt>

                                    <dd class="text-4xl font-extrabold text-blue-600 md:text-5xl">2.1M</dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </section> --}}
            </div>
        </div>
    </div>


</x-app-layout>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    const dashboardData = @json($dashboardData);

    var options = {
        colors: ['#688cff', '#005bfc', '#0617db'],
        series: dashboardData.subjectChart.series,
        chart: {
            type: 'bar',
            height: 350
        },
        plotOptions: {
            bar: {
                horizontal: false,
                borderRadius: 3,
                columnWidth: '75%',
                endingShape: 'rounded'
            },
        },
        dataLabels: {
            enabled: false
        },
        stroke: {
            show: true,
            width: 1,
            radius: 5,
            colors: ['transparent']
        },
        xaxis: {
            categories: dashboardData.subjectChart.categories,
        },
        fill: {
            opacity: 1
        },
        tooltip: {
            shared: true,
            followCursor: true,
            intersect: false
        },
        };

        var chart = new ApexCharts(document.querySelector("#chart"), options);
        chart.render();


        var chart3 = {
            colors: ['#688cff', '#005bfc', '#0617db', '#053385', '#2f9e44'],
            series: dashboardData.printOrderChart.series,
            chart: {
                width: 380,
                height: 350,
                type: 'polarArea'
            },
            labels: dashboardData.printOrderChart.labels,
            fill: {
                opacity: 1
            },
            stroke: {
                width: 1,
                colors: undefined
            },
            yaxis: {
                show: false
            },
            legend: {
                position: 'bottom'
            },
            plotOptions: {
                polarArea: {
                    rings: {
                        strokeWidth: 0
                    },
                    spokes: {
                        strokeWidth: 0
                    },
                }
            },
            theme: {
                monochrome: {
                    enabled: true,
                    shadeTo: 'dark',
                    shadeIntensity: 0.6
                }
            }
        };

        var chart = new ApexCharts(document.querySelector("#chart3"), chart3);
        chart.render();


    var chart1 = {
        title: {
                          text: 'Books',
                          offsetX: 0,
                          style: {
                              fontSize: '24px',
                              color:  '#6b0d00',
                              cssClass: 'text-gray-500',
                              fontFamily: 'Nunito'
                              }
                    },
                    subtitle: {
                      text: 'Books Information',
                      offsetX: 1,
                      style: {
                      fontSize: '14px',
                      color:  '#88888e',
                      cssClass: 'text-gray-500',
                      fontFamily: 'Nunito'
                      }
                  },
                  colors: ["#ff4f36", "#b91e09", "#6b0d00", "#16a349"],
          series: dashboardData.bookSummaryChart.series,
          labels: dashboardData.bookSummaryChart.labels,
          chart: {
          type: 'donut',
          height: 400
        },
        legend: {
            position: 'bottom',
            offsetY: 0,
            height: 30,
          },
        responsive: [{
          breakpoint: 480,
          options: {
            chart: {
              width: 200
            },
            legend: {
              position: 'bottom'
            }
          }
        }]
        };

        var chart2 = {
                     title: {
                          text: 'Warehouses',
                          offsetX: 0,
                          style: {
                              fontSize: '24px',
                              color:  '#ac5709',
                              cssClass: 'text-gray-500',
                              fontFamily: 'Nunito'
                              }
                    },
                    subtitle: {
                      text: 'Warehouses Information',
                      offsetX: 1,
                      style: {
                      fontSize: '14px',
                      color:  '#88888e',
                      cssClass: 'text-gray-500',
                      fontFamily: 'Nunito'
                      }
                  },
                  colors: ["#ffad00", "#ac5709", "#ff8500", "#16a349"],
          series: dashboardData.warehouseChart.series,
          labels: dashboardData.warehouseChart.labels,
          chart: {
          type: 'donut',
          height: 400
        },
        legend: {
            position: 'bottom',
            offsetY: 0,
            height: 30,
          },
        responsive: [{
          breakpoint: 480,
          options: {
            chart: {
              width: 200
            },
            legend: {
              position: 'bottom'
            }
          }
        }]
        };

        var chart = new ApexCharts(document.querySelector("#chart1"), chart1);chart.render();
        var chart = new ApexCharts(document.querySelector("#chart2"), chart2);chart.render();

</script>
