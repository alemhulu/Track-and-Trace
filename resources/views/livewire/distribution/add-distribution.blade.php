<div>
    <x-form.card function="save" :title="$editingDistributionId ? 'Edit Distribution' : 'Add New Distribution'">
        <div class="space-y-8 divide-y divide-gray-200 sm:space-y-5">
            <div>
                @if($editingDistributionId)
                <div class="flex items-center justify-between mb-3">
                    <div class="text-sm text-yellow-700 dark:text-yellow-300 font-semibold">
                        Editing Distribution #{{ $editingDistributionId }}
                    </div>
                    <x-jet-secondary-button type="button" wire:click="cancelEdit">Cancel Edit</x-jet-secondary-button>
                </div>
                @endif
                <div>
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-50">
                        Distribution
                    </h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                        Basic Distribution information
                    </p>
                </div>

                <div class="mt-6 sm:mt-5 space-y-6 sm:space-y-5">
                    <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:pt-5">
                        <x-jet-label value="Name" />
                        <div class="mt-1 sm:mt-0 sm:col-span-2">
                            <x-jet-input type="text" wire:model.defer="name" id="name" class="max-w-xl" />
                            <x-jet-input-error for="name" alert="name" />
                        </div>
                    </div>

                    <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:pt-3">
                        <x-jet-label value="Description" />
                        <div class="mt-1 sm:mt-0 sm:col-span-2">
                            <x-form.textarea name="description" wire:model.defer="description"
                                placeholder="Type Description" row="5" class=" max-w-xl" />
                            <x-jet-input-error for="description" alert="Description" />
                        </div>
                    </div>

                    <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:pt-3">
                        <x-jet-label value="Status" />
                        <div class="mt-1 sm:mt-0 sm:col-span-2">
                            <x-form.select wire:model="is_active" id="is_active" class="max-w-xl">
                                <option value="1">Active</option>
                                <option value="0">In Active</option>
                            </x-form.select>
                            <x-jet-input-error for="is_active" alert="Status" />
                        </div>
                    </div>

                    <div class="border-t border-gray-200 pt-3 mt-5">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-50">
                            Distribution Routes and Steps
                        </h3>
                        <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                            Distribution Steps information
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
                            @forelse($steps as $index => $record)
                            @php
                            $selectedRoute = $routes->firstWhere('id', $record['route_id'] ?? null);
                            @endphp
                            <x-data-table.tr>
                                <td class="px-5 py-2 whitespace-nowrap">
                                    <div class=" rounded-full bg-blue-500 w-10 h-10 flex items-center justify-center">
                                        <span class="text-sm text-blue-50 font-bold"> {{ $index + 1 }}</span>
                                    </div>
                                </td>

                                <td class="px-5 py-2 w-80">
                                    <x-form.select wire:model="steps.{{ $index }}.route_id" id="route_id"
                                        class="max-w-xl min-w-max">
                                        <option value="">select</option>
                                        @foreach($routes as $route)
                                        <option value="{{ $route->id }}">{{ $route->name }}</option>
                                        @endforeach
                                    </x-form.select>
                                    <x-jet-input-error for="steps.{{ $index }}.route_id" alert="Route" />
                                </td>

                                <td class="px-5 py-2 whitespace-nowrap">
                                    <div class="text-sm text-gray-700 dark:text-gray-100">
                                        {{ optional(optional($selectedRoute)->fromWarehouse)->branch ? 'Branch '.optional($selectedRoute->fromWarehouse)->branch : 'N/A' }}
                                    </div>
                                </td>

                                <td class="px-5 py-2 whitespace-nowrap">
                                    <div class="text-sm text-gray-700 dark:text-gray-100">
                                        {{ optional(optional($selectedRoute)->toWarehouse)->branch ? 'Branch '.optional($selectedRoute->toWarehouse)->branch : 'N/A' }}
                                    </div>
                                </td>

                                <td class="px-5 py-2">
                                    <x-jet-danger-button type="button" wire:click="deleteStep({{ $index }})">
                                        Remove
                                    </x-jet-danger-button>
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
    </x-form.card>
</div>
