<div>
    <x-form.card function="addRoute" :title="$editingRouteId ? 'Edit Route' : 'Add New Route'">
        <div class="space-y-8 divide-y divide-gray-200 sm:space-y-5">
            <div>
                @if($editingRouteId)
                <div class="flex items-center justify-between mb-3">
                    <div class="text-sm text-yellow-700 dark:text-yellow-300 font-semibold">
                        Editing Route #{{ $editingRouteId }}
                    </div>
                    <x-jet-secondary-button type="button" wire:click="cancelEdit">Cancel Edit</x-jet-secondary-button>
                </div>
                @endif
                <div>
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-50">
                        Route
                    </h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                        Basic Route information
                    </p>
                </div>

                <div class="mt-6 sm:mt-5 space-y-6 sm:space-y-5">
                    <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:pt-5">
                        <x-jet-label value="Name" />
                        <div class="mt-1 sm:mt-0 sm:col-span-2">
                            <x-jet-input type="text" wire:model.defer="name" id="name" class="max-w-md" />
                            <x-jet-input-error for="name" alert="Route Name" />
                        </div>
                    </div>

                    <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:pt-5">
                        <x-jet-label value="From wearhouse" />
                        <div class="mt-1 sm:mt-0 sm:col-span-2">
                            <x-form.select wire:model="from_warehouse" id="from_warehouse" class="max-w-md">
                                <option value="">select</option>
                                @foreach ($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}">
                                    Branch {{ $warehouse->branch }} -
                                    {{ optional($warehouse->organization)->name ?? 'N/A' }}
                                </option>
                                @endforeach
                            </x-form.select>
                            <x-jet-input-error for="from_warehouse" alert="From Warehouse" />
                        </div>
                    </div>

                    <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:pt-5">
                        <x-jet-label value="To wearhouse" />
                        <div class="mt-1 sm:mt-0 sm:col-span-2">
                            <x-form.select wire:model="to_warehouse" id="to_warehouse" class="max-w-md">
                                <option value="">select</option>
                                @foreach ($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}">
                                    Branch {{ $warehouse->branch }} -
                                    {{ optional($warehouse->organization)->name ?? 'N/A' }}
                                </option>
                                @endforeach
                            </x-form.select>
                            <x-jet-input-error for="to_warehouse" alert="To Warehouse" />
                        </div>
                    </div>

                    <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:pt-3">
                        <x-jet-label value="Discription" />
                        <div class="mt-1 sm:mt-0 sm:col-span-2">
                            <x-form.textarea name="description" wire:model.defer="description"
                                placeholder="Type Description" row="5" class=" max-w-md" />
                            <x-jet-input-error for="description" alert="Route Description" />
                        </div>
                    </div>

                    <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:pt-3">
                        <x-jet-label value="Status" />
                        <div class="mt-1 sm:mt-0 sm:col-span-2">
                            <x-form.select wire:model="is_active" id="is_active" class="max-w-md">
                                <option value="1">Active</option>
                                <option value="0">In Active</option>
                            </x-form.select>
                            <x-jet-input-error for="is_active" alert="Status" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-form.card>
</div>
