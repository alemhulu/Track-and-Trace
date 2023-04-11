<div>
    <section class="bg-white dark:bg-gray-800 rounded-lg">
        <div class="mb-2">
            <h3 class=" text-lg leading-6 font-medium text-gray-900 dark:text-gray-50">
                Distribution
            </h3>
            <p class="max-w-2xl text-sm text-gray-500 dark:text-gray-300">
                Distribution Detail Information
            </p>
        </div>

        <div class="md:px-4 py-5 mx-auto sm:px-6 lg:px-8 mb-3 lg:border border-dashed rounded-md">
            <div class="">
                <dl class="grid grid-cols-1 gap-5 md:grid-cols-7 space-y-4 lg:space-y-0">
                    <div class="flex flex-col md:col-span-3 lg:col-span-2 space-y-2 justify-center">
                        <div>
                            <x-jet-label value="Name" />
                            <div class="sm:mt-0 sm:col-span-2 text-gray-500 dark:text-gray-300">
                                Name of the distribution
                            </div>
                        </div>

                        <div>
                            <x-jet-label value="Package" />
                            <div class="sm:mt-0 sm:col-span-2 text-gray-500 dark:text-gray-300">
                                50 Packages
                            </div>
                        </div>

                        <div>
                            <x-jet-label value="Steps" />
                            <div class=" rounded-full bg-blue-500 w-10 h-10 flex items-center justify-center">
                                <span class="text-sm text-blue-50 font-bold">4</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col md:col-span-4 lg:col-span-2 justify-center">
                        <div>
                            <x-jet-label value="Discription" />
                            <div
                                class="w-full h-auto md:p-3 md:border border-gray-100 rounded-md text-gray-500 dark:text-gray-300 text-left">
                                Lorem ipsum dolor sit, amet consectetur adipisicing elit. Perspiciatis enim, eaque,
                                voluptatibus quo officiis molestias, ipsa quae natus iure incidunt beatae error impedit
                                quod!
                            </div>
                        </div>
                    </div>

                    <div
                        class="flex flex-col md:col-span-4  lg:col-span-2 lg:px-4 text-left  rounded-lg lg:items-center justify-center">
                        <x-book.book-info image="/biology-grade-10.jpg" grade="Grade 10" subject="Biology"
                            type="Student Text Book" edition="1st Edition 2013" ISBN="4820715" />
                    </div>

                    <div
                        class="flex flex-col lg:px-4 md:col-span-3 lg:col-span-1  rounded-lg lg:items-center lg:justify-center">
                        <div>
                            <x-jet-label value="Status" />
                            <div class="sm:mt-0 sm:col-span-2 text-gray-500">
                                <x-button btnType="success" class="py-1 relative pl-6 pr-2 font-bold">
                                    <i class="fi fi-rr-checkbox flex inset-0 top-1 left-1 absolute text-base"></i>
                                    100 %
                                </x-button>
                            </div>
                        </div>
                    </div>

                </dl>
            </div>
        </div>
    </section>
</div>