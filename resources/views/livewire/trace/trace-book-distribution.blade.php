<div>
    <x-form.table title="Book Distribution Information List">
        <x-slot name="tableHeaders">
            <x-data-table.th scope="col">#</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Name') }}</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Steps ') }}</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Packages') }}</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Books') }}</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Status') }}</x-data-table.th>
            <x-data-table.th scope="col" class="sr-only">{{__('Action') }}</x-data-table.th>
        </x-slot>

        <x-slot name="tableRows">
            @php $i = 1; $record = 1;@endphp
            {{-- @forelse($books as $record) --}}
            <x-data-table.tr>
                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-700">1</div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-600 font-semibold">Dist-MoE-To-Addis-Yeka-School-1</div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-700">
                        <div class=" rounded-full bg-blue-500 w-10 h-10 flex items-center justify-center">
                            <span class="text-sm text-blue-50 font-bold">4</span>
                        </div>
                    </div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-500">40 Packages
                    </div>
                </td>

                <td class="px-5 py-2 flex-wrap">
                    <div class="text-sm text-gray-500">513 Books</div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <x-button btnType="success" class="py-1 relative pl-6 pr-2 font-bold">
                        <i class="fi fi-rr-checkbox flex inset-0 top-1 left-1 absolute text-base"></i>100 %
                    </x-button>
                </td>

                <td class="px-5 py-2">
                    <x-action.table-button id="{{ $record }}" view="showDistribution" />
                </td>
            </x-data-table.tr>

            <x-data-table.tr>
                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-700">2</div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-600 font-semibold">Dist-MoE-To-Addis-Yeka-School-1</div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-700">
                        <div class=" rounded-full bg-blue-500 w-10 h-10 flex items-center justify-center">
                            <span class="text-sm text-blue-50 font-bold">4</span>
                        </div>
                    </div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-500">40 Packages
                    </div>
                </td>

                <td class="px-5 py-2 flex-wrap">
                    <div class="text-sm text-gray-500">513 Books</div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <x-button btnType="success" class="py-1 relative pl-6 pr-2 font-bold">
                        <i class="fi fi-rr-shield-exclamation flex inset-0 top-1 left-1 absolute text-base"></i>94 %
                    </x-button>
                </td>

                <td class="px-5 py-2">
                    <x-action.table-button id="{{ $record }}" view="viewBook" />
                </td>
            </x-data-table.tr>

            <x-data-table.tr>
                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-700">3</div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-600 font-semibold">Minilik High School Distribution Steps</div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-700">
                        <div class=" rounded-full bg-blue-500 w-10 h-10 flex items-center justify-center">
                            <span class="text-sm text-blue-50 font-bold">4</span>
                        </div>
                    </div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-500">40 Packages
                    </div>
                </td>

                <td class="px-5 py-2 flex-wrap">
                    <div class="text-sm text-gray-500">513 Books</div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <x-button btnType="warning" class="py-1 relative pl-6 pr-2 font-bold">
                        <i class="fi fi-rr-shield-exclamation flex inset-0 top-1 left-1 absolute text-base"></i>70 %
                    </x-button>
                </td>

                <td class="px-5 py-2">
                    <x-action.table-button id="{{ $record }}" view="viewBook" />
                </td>
            </x-data-table.tr>

            <x-data-table.tr>
                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-700">4</div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-600 font-semibold">Bishoftu Priparatory School Book Distribution</div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-700">
                        <div class=" rounded-full bg-blue-500 w-10 h-10 flex items-center justify-center">
                            <span class="text-sm text-blue-50 font-bold">6</span>
                        </div>
                    </div>
                </td>

                <td class="px-5 py-2 flex-wrap">
                    <div class="text-sm text-gray-500">123 Packages</div>
                </td>

                <td class="px-5 py-2 flex-wrap">
                    <div class="text-sm text-gray-500">5,043 Books</div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <x-button btnType="danger" class="py-1 relative pl-6 pr-2 font-bold">
                        <i class="fi fi-rr-shield-exclamation flex inset-0 top-1 left-1 absolute text-base"></i>40 %
                    </x-button>
                </td>

                <td class="px-5 py-2">
                    <x-action.table-button id="{{ $record }}" view="viewBook" />
                </td>
            </x-data-table.tr>
            {{-- @empty --}}
            {{-- <x-data-table.empty colspan=6 /> --}}
            {{-- @endforelse --}}
        </x-slot>
    </x-form.table>
</div>