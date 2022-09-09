<div>
    <x-form.card function="save" title="Add New Distribution">
        <form class=" space-y-8 divide-y divide-gray-200">
            <div class="space-y-8 divide-y divide-gray-200 sm:space-y-5">
                <div>
                    <div>
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            Distribution
                        </h3>
                        <p class="mt-1 max-w-2xl text-sm text-gray-500">
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
                                        class="bg-gray-200 relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200"
                                        :class="toggle && 'bg-blue-600'" role="switch" aria-checked="0">
                                        <span class="sr-only">Use setting</span>
                                        <span aria-hidden="true"
                                            class="pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200"
                                            :class="toggle && 'translate-x-5'"></span>
                                    </button>
                                    <span class="text-gray-600 font-semibold"
                                        x-text="toggle === 1 ? 'Active' : 'In Active'"> </span>
                                    <input type="hidden" wire:model="is_active" name="is_active" class="hidden"
                                        x-model.number="toggle" />
                                    <x-jet-input-error for="is_active" alert="Active" />
                                </div>
                            </div>
                        </div>

                        <div class=" border-t border-gray-200 pt-3 mt-5">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">
                                Distribution Routes and Steps
                            </h3>
                            <p class="mt-1 max-w-2xl text-sm text-gray-500">
                                Basic Distribution information
                            </p>
                        </div>

                        <x-form.table title="Distribution Steps & Route" :search=false :entries=false>
                            <x-slot name="tableHeaders">
                                <x-data-table.th scope="col">{{ __('Steps') }}</x-data-table.th>
                                <x-data-table.th scope="col"> {{__('Routes') }}</x-data-table.th>
                                <x-data-table.th scope="col"> {{__('From') }}</x-data-table.th>
                                <x-data-table.th scope="col"> {{__('To ') }}</x-data-table.th>
                                <x-data-table.th scope="col" class="sr-only">{{__('Action') }}</x-data-table.th>
                            </x-slot>

                            <x-slot name="tableRows">
                                @php $i = 1; @endphp
                                @forelse($steps as $index=>$record)
                                <x-data-table.tr>
                                    <td class="px-5 py-2 whitespace-nowrap">
                                        <div
                                            class=" rounded-full bg-blue-500 w-10 h-10 flex items-center justify-center">
                                            <span class="text-sm text-blue-50 font-bold"> {{ $index + 1 }}</span>
                                        </div>
                                    </td>

                                    <td class="px-5 py-2 whitespace-nowrap">
                                        <x-form.select wire:model="steps.{{ $index }}.route_id" id="route_id"
                                            class="max-w-xl">
                                            <option>select</option>
                                            <option>Route 1</option>
                                            <option>Route 2</option>
                                        </x-form.select>
                                        <x-jet-input-error for="steps.{{ $index }}.route_id" alert="Route" />
                                    </td>

                                    <td class="px-5 py-2 whitespace-nowrap">
                                        <div class="text-sm text-gray-700">WareHouse {{ $index }}</div>
                                    </td>

                                    <td class="px-5 py-2 whitespace-nowrap">
                                        <div class="text-sm text-gray-700">WareHouse {{ $index + 1 }}</div>
                                    </td>

                                    <td class="px-5 py-2">
                                        <x-action.table-button id="{{ $index }}" delete="deleteStep" />
                                    </td>
                                </x-data-table.tr>
                                @empty
                                <x-data-table.empty colspan=5 />
                                @endforelse
                            </x-slot>

                            <div class="ml-4">
                                <x-action.table-button add="addStep" text="Add Step" />
                            </div>
                        </x-form.table>
                    </div>
                </div>
            </div>
        </form>
    </x-form.card>
</div>