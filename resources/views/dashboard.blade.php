<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:text-gray-200">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="">
        <div class="w-auto">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg dark:bg-gray-800">
                <section aria-labelledby="profile-overview-title">
                    <div class="rounded-lg overflow-hidden shadow">
                        <h2 class="sr-only" id="profile-overview-title">Profile Overview</h2>
                        <div class=" p-6">
                            <div class="sm:flex sm:items-center sm:justify-between">
                                <div class="sm:flex sm:space-x-5">
                                    <div class="flex-shrink-0">
                                        @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                                        <img class="mx-auto h-20 w-20 rounded-full drop-shadow-xl" src=" {{
                                            Auth::user()->profile_photo_url }}" alt="">
                                        @else
                                        <img class="mx-auto h-20 w-20 rounded-full drop-shadow-xl"
                                            src="https://cdn.pixabay.com/photo/2015/10/05/22/37/blank-profile-picture-973460_1280.png"
                                            alt="">
                                        @endif
                                    </div>
                                    <div class="mt-4 text-center sm:mt-0 sm:pt-1 sm:text-left">
                                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Welcome Back
                                        </p>
                                        <p
                                            class="text-xl font-bold text-gray-900 dark:text-gray-100 sm:text-2xl drop-shadow-lg">
                                            {{ Auth::user()->name }}
                                        </p>
                                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                                            {{ Auth::user()->email }}
                                        </p>
                                    </div>
                                </div>
                                <div class="mt-5 flex justify-center sm:mt-0">
                                    <a href="{{ route('profile.show') }}"
                                        class="flex justify-center items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 dark:bg-gray-300 dark:text-gray-900 dark:border-gray-600">
                                        Profile
                                    </a>
                                </div>
                            </div>
                        </div>

                    </div>
                </section>

                <section class="rounded-lg">
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
                </section>
            </div>
        </div>
    </div>
</x-app-layout>