<div>
    <x-form.table title="Book Distribution Information List" :entries=false :search=false>
        <x-slot name="tableHeaders">
            <x-data-table.th scope="col">Steps</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Route') }}</x-data-table.th>
            <x-data-table.th scope="col"> {{__('From ') }}</x-data-table.th>
            <x-data-table.th scope="col"> {{__('To') }}</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Status') }}</x-data-table.th>
            <x-data-table.th scope="col" class="sr-only">{{__('Action') }}</x-data-table.th>
        </x-slot>

        <x-slot name="tableRows">
            @php $i = 1; $record = 1;@endphp
            {{-- @forelse($books as $record) --}}
            <x-data-table.tr>
                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-700">
                        <div class=" rounded-full bg-blue-500 w-10 h-10 flex items-center justify-center">
                            <span class="text-sm text-blue-50 font-bold">1</span>
                        </div>
                    </div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-600 font-semibold">From MoE to Oromia Region</div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <x-trace.from-info company="MoE" />
                </td>

                <td class="px-5 py-2 flex-wrap">
                    <x-trace.to-info company="Oromia Region" />
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <x-button btnType="success" class="py-1 relative pl-6 pr-2 font-bold">
                        <i class="fi fi-rr-checkbox flex inset-0 top-1 left-1 absolute text-base"></i>Recived
                    </x-button>
                </td>

                <td class="px-5 py-2">
                    <x-action.table-button id="{{ $record }}" view="showDistribution" />
                </td>
            </x-data-table.tr>

            <x-data-table.tr>
                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-700">
                        <div class=" rounded-full bg-blue-500 w-10 h-10 flex items-center justify-center">
                            <span class="text-sm text-blue-50 font-bold">2</span>
                        </div>
                    </div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-600 font-semibold">From Oromia Region to East Shoa Zone</div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <x-trace.from-info company="Oromia Region" />
                </td>

                <td class="px-5 py-2 flex-wrap">
                    <x-trace.to-info company="East Shoa Zone" />
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <x-button btnType="success" class="py-1 relative pl-6 pr-2 font-bold">
                        <i class="fi fi-rr-checkbox flex inset-0 top-1 left-1 absolute text-base"></i>Recived
                    </x-button>
                </td>

                <td class="px-5 py-2">
                    <x-action.table-button id="{{ $record }}" view="viewBook" />
                </td>
            </x-data-table.tr>

            <x-data-table.tr>
                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-700">
                        <div class=" rounded-full bg-blue-500 w-10 h-10 flex items-center justify-center">
                            <span class="text-sm text-blue-50 font-bold">3</span>
                        </div>
                    </div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-600 font-semibold">From East Shoa Zone to Adaa Woreda</div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <x-trace.from-info company="East Shoa Zone" />
                </td>

                <td class="px-5 py-2 flex-wrap">
                    <x-trace.to-info company="Adaa Woreda" />
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <x-button btnType="warning" class="py-1 relative pl-6 pr-2 font-bold">
                        <i class="fi fi-rr-paper-plane flex inset-0 top-1 left-1 absolute text-base"></i>Pending
                    </x-button>
                </td>

                <td class="px-5 py-2">
                    <x-action.table-button id="{{ $record }}" view="viewBook" />
                </td>
            </x-data-table.tr>

            <x-data-table.tr>
                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-700">
                        <div class=" rounded-full bg-blue-500 w-10 h-10 flex items-center justify-center">
                            <span class="text-sm text-blue-50 font-bold">4</span>
                        </div>
                    </div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-600 font-semibold">From Adaa Woreda to Bishoftu Priparatory School
                    </div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <x-trace.from-info company="Adaa Woreda" />
                </td>

                <td class="px-5 py-2 flex-wrap">
                    <x-trace.to-info company="Bishoftu Priparatory School" />
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <x-button btnType="warning" class="py-1 relative pl-6 pr-2 font-bold">
                        <i class="fi fi-rr-paper-plane flex inset-0 top-1 left-1 absolute text-base"></i>Pending
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