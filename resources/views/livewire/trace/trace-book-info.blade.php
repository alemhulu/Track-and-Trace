<div>
    <section class="bg-white dark:bg-gray-800 rounded-lg">
        <div class="mt-6  pt-3">
            <h3 class=" text-lg leading-6 font-medium text-gray-900">
                Book
            </h3>
            <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-300">
                Book Information and Status
            </p>
        </div>
        <div class=" px-4 py-5 mx-auto sm:px-6 lg:px-8 mb-3">
            <div class="mt-4">
                <dl class="grid grid-cols-1 gap-5 sm:grid-cols-5">
                    <div class="flex flex-col">
                        <x-book.book-info :image="$image" :grade="$gradeName" :subject="$subjectName" :type="$type"
                            :edition="$edition" :ISBN="$isbn" />
                    </div>

                    <div
                        class="flex flex-col px-4 py-8 text-center border border-blue-200 rounded-lg items-center justify-center">
                        <dt class="order-last text-lg font-medium text-gray-500 dark:text-gray-300">
                            Total Printed
                        </dt>

                        <dd class="text-4xl font-extrabold text-blue-500 md:text-5xl">
                            {{ number_format($totalPrinted) }}
                        </dd>
                    </div>

                    <div
                        class="flex flex-col px-4 py-8 text-center border border-blue-200 rounded-lg items-center justify-center">
                        <dt class="order-last text-lg font-medium text-gray-500 dark:text-gray-300">
                            Total Distributed
                        </dt>

                        <dd class="text-4xl font-extrabold text-blue-500 md:text-5xl">
                            {{ number_format($totalDistributed) }}
                        </dd>
                    </div>

                    <div
                        class="flex flex-col px-4 py-8 text-center border border-blue-200 rounded-lg items-center justify-center">
                        <dt class="order-last text-lg font-medium text-gray-500 dark:text-gray-300">
                            Total In Stock
                        </dt>

                        <dd class="text-4xl font-extrabold text-blue-500 md:text-5xl">{{ number_format($totalInStock) }}
                        </dd>
                    </div>

                    <div
                        class="flex flex-col px-4 py-8 text-center border border-blue-200 rounded-lg items-center justify-center">
                        <dt class="order-last text-lg font-medium text-gray-500 dark:text-gray-300">
                            Total On Student Hand
                        </dt>

                        <dd class="text-4xl font-extrabold text-blue-500 md:text-5xl">
                            {{ number_format($totalOnStudentHand) }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </section>
</div>
