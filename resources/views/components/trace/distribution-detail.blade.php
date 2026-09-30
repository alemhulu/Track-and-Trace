@props(['distribution'])

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
            <div>
                <dl class="grid grid-cols-1 gap-5 md:grid-cols-3">
                    <div class="flex flex-col space-y-2 justify-center">
                        <div>
                            <x-jet-label value="Name" />
                            <div class="sm:mt-0 sm:col-span-2 text-gray-500 dark:text-gray-300">
                                {{ $distribution->name }}
                            </div>
                        </div>

                        <div>
                            <x-jet-label value="Created" />
                            <div class="sm:mt-0 sm:col-span-2 text-gray-500 dark:text-gray-300">
                                {{ optional($distribution->created_at)->format('Y-m-d H:i') ?? 'N/A' }}
                            </div>
                        </div>

                        <div>
                            <x-jet-label value="Steps" />
                            <div class=" rounded-full bg-blue-500 w-10 h-10 flex items-center justify-center">
                                <span class="text-sm text-blue-50 font-bold">{{ $distribution->steps->count() }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col justify-center">
                        <div>
                            <x-jet-label value="Description" />
                            <div
                                class="w-full h-auto md:p-3 md:border border-gray-100 rounded-md text-gray-500 dark:text-gray-300 text-left">
                                {{ $distribution->description ?: 'No description provided.' }}
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col rounded-lg md:items-center md:justify-center">
                        <div>
                            <x-jet-label value="Status" />
                            <div class="sm:mt-0 sm:col-span-2 text-gray-500">
                                @if($distribution->is_active)
                                <x-button btnType="success" class="py-1 relative pl-6 pr-2 font-bold">
                                    <i class="fi fi-rr-checkbox flex inset-0 top-1 left-1 absolute text-base"></i>
                                    Active
                                </x-button>
                                @else
                                <x-button btnType="danger" class="py-1 relative pl-6 pr-2 font-bold">
                                    <i class="fi fi-rr-cross-circle flex inset-0 top-1 left-1 absolute text-base"></i>
                                    InActive
                                </x-button>
                                @endif
                            </div>
                        </div>
                    </div>
                </dl>
            </div>
        </div>
    </section>
</div>
