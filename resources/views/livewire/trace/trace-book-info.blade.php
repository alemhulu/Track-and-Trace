<div>
    <section class="bg-white dark:bg-gray-800 rounded-lg">
        <div class="mt-6  pt-3">
            <h3 class=" text-lg leading-6 font-medium text-gray-900">
                Book
            </h3>
            <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-300 dark:text-gray-400">
                Book Information and Status
            </p>
        </div>
        <div class=" px-4 py-5 mx-auto sm:px-6 lg:px-8 mb-3">
            <div class="mt-4">
                <dl class="grid grid-cols-1 gap-5 sm:grid-cols-5">
                    <div class="flex flex-col">
                        <x-book.book-info image="/biology-grade-10.jpg" grade="Grade 10" subject="Biology"
                            type="Student Text Book" edition="1st Edition 2013" ISBN="4820715" />
                    </div>

                    <div
                        class="flex flex-col px-4 py-8 text-center border border-blue-200 rounded-lg items-center justify-center">
                        <dt class="order-last text-lg font-medium text-gray-500 dark:text-gray-300">
                            Total Printed
                        </dt>

                        <dd class="text-4xl font-extrabold text-blue-500 md:text-5xl">
                            6,582
                        </dd>
                    </div>

                    <div
                        class="flex flex-col px-4 py-8 text-center border border-blue-200 rounded-lg items-center justify-center">
                        <dt class="order-last text-lg font-medium text-gray-500 dark:text-gray-300">
                            Total Distributed
                        </dt>

                        <dd class="text-4xl font-extrabold text-blue-500 md:text-5xl">
                            6,453
                        </dd>
                    </div>

                    <div
                        class="flex flex-col px-4 py-8 text-center border border-blue-200 rounded-lg items-center justify-center">
                        <dt class="order-last text-lg font-medium text-gray-500 dark:text-gray-300">
                            Total In Stock
                        </dt>

                        <dd class="text-4xl font-extrabold text-blue-500 md:text-5xl">305</dd>
                    </div>

                    <div
                        class="flex flex-col px-4 py-8 text-center border border-blue-200 rounded-lg items-center justify-center">
                        <dt class="order-last text-lg font-medium text-gray-500 dark:text-gray-300">
                            Total On Student Hand
                        </dt>

                        <dd class="text-4xl font-extrabold text-blue-500 md:text-5xl">6,277</dd>
                    </div>
                </dl>
            </div>
        </div>
    </section>
</div>