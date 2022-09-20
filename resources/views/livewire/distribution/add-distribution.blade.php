<div>
    <x-form.card function="save" title="Add New Distribution">
        <form class=" space-y-8 divide-y divide-gray-200">
            <div class="space-y-8 divide-y divide-gray-200 sm:space-y-5">
                <div>
                    <div>
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-50">
                            Distribution
                        </h3>
                        <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                            Basic Distribution information
                        </p>
                    </div>

                    <div class="mt-6 sm:mt-5 space-y-6 sm:space-y-5">
                        <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start  sm:pt-5">
                            <x-jet-label value="Name" />
                            <div class="mt-1 sm:mt-0 sm:col-span-2">
                                <x-jet-input type="text" wire:model="name" id="name" class="max-w-xl" />
                                <x-jet-input-error for="name" alert="name" />
                            </div>
                        </div>

                        {{-- <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start  sm:pt-5">
                            <x-jet-label value="From wearhouse" />
                            <div class="mt-1 sm:mt-0 sm:col-span-2">
                                <x-form.select wire:model="from_warehouse" id="from_warehouse" class="max-w-md">
                                    <option>select</option>
                                    <option>wearhouse 1</option>
                                    <option>wearhouse 2</option>
                                </x-form.select>
                            </div>
                        </div> --}}

                        <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start  sm:pt-3">
                            <x-jet-label value="Description" />
                            <div class="mt-1 sm:mt-0 sm:col-span-2">
                                <x-form.textarea name="description" wire:model.defer="description"
                                    placeholder="Type Description" row="5" class=" max-w-xl" />
                                <x-jet-input-error for="description" alert="Description" />
                            </div>
                        </div>

                        <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start  sm:pt-3">
                            <x-jet-label value="Status" />
                            <div class="mt-1 sm:mt-0 sm:col-span-2">
                                <div class="flex items-center gap-x-2" x-data="{ toggle: 0 }">
                                    <button x-on:click="toggle === 0 ? toggle = 1  : toggle = 0" type="button"
                                        class="bg-gray-200 dark:bg-gray-400 relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200"
                                        :class="toggle && 'bg-blue-600 dark:bg-blue-500'" role="switch"
                                        aria-checked="0">
                                        <span class="sr-only">Use setting</span>
                                        <span aria-hidden="true"
                                            class="pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200"
                                            :class="toggle && 'translate-x-5'"></span>
                                    </button>
                                    <span class="text-gray-600 dark:text-gray-300 font-semibold"
                                        x-text="toggle === 1 ? 'Active' : 'In Active'"> </span>
                                    <input type="hidden" name="is_active" class="hidden" x-model.number="toggle" />
                                </div>
                            </div>
                        </div>

                        <livewire:distribution.distribution-steps>
                    </div>
                </div>
            </div>
        </form>
    </x-form.card>
</div>