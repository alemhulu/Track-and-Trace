<div>
    <x-form.card function="noop" title="Route Detail" :buttons="false">
        <div class="space-y-8 divide-y divide-gray-200 sm:space-y-5">
            <div>
                <div>
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-50">
                        {{ $routeRecord->name }}
                    </h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                        {{ $routeRecord->description ?: 'No description provided.' }}
                    </p>
                </div>

                <div class="mt-6 sm:mt-5 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="rounded-md border border-dashed border-gray-300 dark:border-gray-700 p-4">
                        <div class="text-xs uppercase text-gray-500 dark:text-gray-400">From Warehouse</div>
                        <div class="text-sm font-semibold text-gray-700 dark:text-gray-100 mt-1">
                            Branch {{ optional($routeRecord->fromWarehouse)->branch ?? 'N/A' }}
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-300 mt-1">
                            {{ optional(optional($routeRecord->fromWarehouse)->organization)->name ?? 'N/A' }}
                        </div>
                    </div>

                    <div class="rounded-md border border-dashed border-gray-300 dark:border-gray-700 p-4">
                        <div class="text-xs uppercase text-gray-500 dark:text-gray-400">To Warehouse</div>
                        <div class="text-sm font-semibold text-gray-700 dark:text-gray-100 mt-1">
                            Branch {{ optional($routeRecord->toWarehouse)->branch ?? 'N/A' }}
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-300 mt-1">
                            {{ optional(optional($routeRecord->toWarehouse)->organization)->name ?? 'N/A' }}
                        </div>
                    </div>
                </div>

                <div class="mt-4 flex items-center gap-3">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Status:</div>
                    @if($routeRecord->is_active)
                    <x-button btnType="success" class="py-1">Active</x-button>
                    @else
                    <x-button btnType="danger" class="py-1">InActive</x-button>
                    @endif
                </div>
            </div>

            <div class="pt-4">
                <x-form.table title="Distribution Usage" :search="false" :entries="false">
                    <x-slot name="tableHeaders">
                        <x-data-table.th scope="col">Distribution</x-data-table.th>
                        <x-data-table.th scope="col">Step Order</x-data-table.th>
                        <x-data-table.th scope="col">Distribution Status</x-data-table.th>
                        <x-data-table.th scope="col">Action</x-data-table.th>
                    </x-slot>

                    <x-slot name="tableRows">
                        @forelse($stepUsages as $usage)
                        <x-data-table.tr>
                            <td class="px-5 py-2 whitespace-nowrap">
                                <div class="text-sm text-gray-700 dark:text-gray-100">
                                    {{ optional($usage->distribution)->name ?? 'N/A' }}
                                </div>
                            </td>

                            <td class="px-5 py-2 whitespace-nowrap">
                                <div class="text-sm text-gray-700 dark:text-gray-100">
                                    {{ $usage->step_order }}
                                </div>
                            </td>

                            <td class="px-5 py-2 whitespace-nowrap">
                                @if(optional($usage->distribution)->is_active)
                                <x-button btnType="success" class="py-1">Active</x-button>
                                @else
                                <x-button btnType="danger" class="py-1">InActive</x-button>
                                @endif
                            </td>

                            <td class="px-5 py-2 whitespace-nowrap">
                                @if($usage->distribution)
                                <a href="{{ route('distribution-details.show', $usage->distribution) }}"
                                    class="text-sm font-semibold text-blue-600 hover:text-blue-800 dark:text-blue-300 dark:hover:text-blue-200">
                                    Open Detail
                                </a>
                                @else
                                <span class="text-sm text-gray-400">N/A</span>
                                @endif
                            </td>
                        </x-data-table.tr>
                        @empty
                        <x-data-table.empty colspan=4 />
                        @endforelse
                    </x-slot>
                </x-form.table>
            </div>
        </div>
    </x-form.card>
</div>
