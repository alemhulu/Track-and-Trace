<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:text-gray-200">
      {{ __('Dashboard') }}
    </h2>
  </x-slot>

  <div class="p-5 gap-5 grid grid-cols-3 bg-white my-5">
    {!! DNS2D::getBarcodeSVG('0000010000001', 'PDF417', 2, 0.5, 1 , true)!!}

  </div>

  <div class="mb-8">
    <div class="w-auto">
      <div class=" overflow-hidden shadow-xl sm:rounded-lg dark:bg-gray-800">
        <section>
          <div class=" rounded-lg grid grid-cols-6 gap-5">
            <div class="col-span-6 lg:col-span-4 p-4 rounded-lg shadow-md bg-white dark:bg-gray-900" id="chart">
            </div>
            <div class="col-span-6 lg:col-span-2 p-4 rounded-lg shadow-md bg-white dark:bg-gray-900" id="chart3">
            </div>
            <div class="col-span-6 md:col-span-3 p-4 rounded-lg shadow-md bg-white dark:bg-gray-900" id="chart1">
            </div>
            <div class="col-span-6 md:col-span-3 p-4 rounded-lg shadow-md bg-white dark:bg-gray-900" id="chart2">
            </div>
          </div>
        </section>

        {{-- <section class="rounded-lg">
                    <div class=" px-4 py-5 mx-auto sm:px-6 lg:px-8 mb-3">
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
                    <div class="px-4 py-5 mx-auto sm:px-6 lg:px-8 mb-3">
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
                    <div class="px-4 py-5 mx-auto sm:px-6 lg:px-8 mb-3">
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
  var options = {
        colors: ['#688cff', '#005bfc', '#0617db'],
          series: [ { name: 'Printed', data: [3550, 4100, 3690, 2226, 4445, 7748, 5290, 5553, 8941] },
                        { name: 'Distributed', data: [3050, 4001, 2236, 2600, 4000, 3008, 4452, 2253, 4991] },
                        { name: 'Store', data: [1350, 1141, 1036, 1026, 1045, 1048, 1152, 1053, 1041] }
                    ],
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
          categories: ['Amharic', 'Biology', 'English', 'Chemistry', 'Geogarphy', 'Mathimatics', 'Physics', 'Civics', 'History'],
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
            colors: ['#688cff', '#005bfc', '#0617db', '#053385'],
          series: [70, 67, 40, 62,],
          chart: {
          width: 380,
          height: 350,
          type: 'polarArea'
        },
        labels: ['Orders', 'Accepted', 'Rejected', 'Printed'],
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
          series: [210000, 240000, 210000],
          labels: ["Stock", "Distributed", "On Students Hand"],
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
                          text: 'Wearhouses',
                          offsetX: 0,
                          style: {
                              fontSize: '24px',
                              color:  '#ac5709',
                              cssClass: 'text-gray-500',
                              fontFamily: 'Nunito'
                              }
                    },
                    subtitle: {
                      text: 'Wearhouses Informations',
                      offsetX: 1,
                      style: {
                      fontSize: '14px',
                      color:  '#88888e',
                      cssClass: 'text-gray-500',
                      fontFamily: 'Nunito'
                      }
                  },
                  colors: ["#ffad00", "#ac5709", "#ff8500", "#16a349"],
          series: [700, 2400],
          labels: ["Wearhouses", "Stores"],
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