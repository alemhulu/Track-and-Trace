<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="">
        <div class="w-auto">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <section aria-labelledby="profile-overview-title">
                    <div class="rounded-lg bg-white overflow-hidden shadow">
                        <h2 class="sr-only" id="profile-overview-title">Profile Overview</h2>
                        <div class="bg-white p-6">
                            <div class="sm:flex sm:items-center sm:justify-between">
                                <div class="sm:flex sm:space-x-5">
                                    <div class="flex-shrink-0">
                                        <img class="mx-auto h-20 w-20 rounded-full"
                                            src="https://cdn.pixabay.com/photo/2015/10/05/22/37/blank-profile-picture-973460_1280.png"
                                            alt="">
                                    </div>
                                    <div class="mt-4 text-center sm:mt-0 sm:pt-1 sm:text-left">
                                        <p class="text-sm font-medium text-gray-600">Welcome back,</p>
                                        <p class="text-xl font-bold text-gray-900 sm:text-2xl">{{ Auth::user()->name }}
                                        </p>
                                        <p class="text-sm font-medium text-gray-600">{{ Auth::user()->email }}</p>
                                    </div>
                                </div>
                                <div class="mt-5 flex justify-center sm:mt-0">
                                    <a href="#"
                                        class="flex justify-center items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                        View profile
                                    </a>
                                </div>
                            </div>
                        </div>

                    </div>
                </section>

                <section class="bg-white rounded-lg">
                    <div class="max-w-screen-xl px-4 py-5 mx-auto sm:px-6 lg:px-8 mb-3">
                        <div class="mt-4">
                            <p class="text-xl font-bold text-gray-900 sm:text-2xl">Print Orders
                            </p>
                            <dl class="grid grid-cols-1 gap-4 sm:grid-cols-4">
                                <div class="flex flex-col px-4 py-8 text-center border border-gray-200 rounded-lg">
                                    <dt class="order-last text-lg font-medium text-gray-500">
                                        Total Orders
                                    </dt>

                                    <dd class="text-4xl font-extrabold text-gray-600 md:text-5xl">
                                        70
                                    </dd>
                                </div>

                                <div class="flex flex-col px-4 py-8 text-center border border-green-200 rounded-lg">
                                    <dt class="order-last text-lg font-medium text-gray-500">
                                        Total Accepted
                                    </dt>

                                    <dd class="text-4xl font-extrabold text-green-600 md:text-5xl">
                                        67
                                    </dd>
                                </div>

                                <div class="flex flex-col px-4 py-8 text-center border border-green-200 rounded-lg">
                                    <dt class="order-last text-lg font-medium text-gray-500">
                                        Total Printed
                                    </dt>

                                    <dd class="text-4xl font-extrabold text-green-600 md:text-5xl">62</dd>
                                </div>

                                <div class="flex flex-col px-4 py-8 text-center border border-red-200 rounded-lg">
                                    <dt class="order-last text-lg font-medium text-gray-500">
                                        Total Rejected
                                    </dt>

                                    <dd class="text-4xl font-extrabold text-red-600 md:text-5xl">5</dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </section>

                <section class="bg-white rounded-lg">
                    <div class="max-w-screen-xl px-4 py-5 mx-auto sm:px-6 lg:px-8 mb-3">
                        <div class="mt-4">
                            <p class="text-xl font-bold text-gray-900 sm:text-2xl">Books
                            </p>
                            <dl class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                                <div class="flex flex-col px-4 py-8 text-center border border-gray-200 rounded-lg">
                                    <dt class="order-last text-lg font-medium text-gray-500">
                                        Total In Stock
                                    </dt>

                                    <dd class="text-4xl font-extrabold text-gray-600 md:text-5xl">
                                        2.1M
                                    </dd>
                                </div>

                                <div class="flex flex-col px-4 py-8 text-center border border-yellow-200 rounded-lg">
                                    <dt class="order-last text-lg font-medium text-gray-500">
                                        Total Distributed
                                    </dt>

                                    <dd class="text-4xl font-extrabold text-yellow-600 md:text-5xl">2.4M</dd>
                                </div>

                                <div class="flex flex-col px-4 py-8 text-center border border-yellow-200 rounded-lg">
                                    <dt class="order-last text-lg font-medium text-gray-500">
                                        Total on Students Hand
                                    </dt>

                                    <dd class="text-4xl font-extrabold text-yellow-600 md:text-5xl">2.1M</dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </section>

                <section class="bg-white rounded-lg">
                    <div class="max-w-screen-xl px-4 py-5 mx-auto sm:px-6 lg:px-8 mb-3">
                        <div class="mt-4">
                            <p class="text-xl font-bold text-gray-900 sm:text-2xl">Wearhouses
                            </p>
                            <dl class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                                <div class="flex flex-col px-4 py-8 text-center border border-gray-200 rounded-lg">
                                    <dt class="order-last text-lg font-medium text-gray-500">
                                        Total Wearhouses
                                    </dt>

                                    <dd class="text-4xl font-extrabold text-gray-600 md:text-5xl">
                                        700
                                    </dd>
                                </div>

                                <div class="flex flex-col px-4 py-8 text-center border border-blue-200 rounded-lg">
                                    <dt class="order-last text-lg font-medium text-gray-500">
                                        Total Stores
                                    </dt>

                                    <dd class="text-4xl font-extrabold text-blue-600 md:text-5xl">2.4K</dd>
                                </div>

                                <div class="flex flex-col px-4 py-8 text-center border border-blue-200 rounded-lg">
                                    <dt class="order-last text-lg font-medium text-gray-500">
                                        Total Books in Stores
                                    </dt>

                                    <dd class="text-4xl font-extrabold text-blue-600 md:text-5xl">2.1M</dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</x-app-layout>