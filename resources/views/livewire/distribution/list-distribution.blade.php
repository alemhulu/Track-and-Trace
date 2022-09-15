<div>
    <x-form.table title="Distribution List">
        <x-slot name="tableHeaders">
            <x-data-table.th scope="col">#</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Name') }}</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Description') }}</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Steps ') }}</x-data-table.th>
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
                    <div class="text-sm text-gray-500">Distribution from MoE through Addiss Abeba , Yeka all the way to
                        School One</div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-700">
                        <div class=" rounded-full bg-blue-500 w-10 h-10 flex items-center justify-center">
                            <span class="text-sm text-blue-50 font-bold">4</span>
                        </div>
                    </div>
                </td>


                <td class="px-5 py-2 whitespace-nowrap">
                    <x-button btnType="success" class="py-1">Active</x-button>
                </td>

                <td class="px-5 py-2">
                    <x-action.table-button id="{{ $record }}" view="viewBook" edit="editBook" delete="deleteBook" />
                </td>
            </x-data-table.tr>

            <x-data-table.tr>
                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-700">2</div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-600 font-semibold">Minilik High School Distribution Steps</div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-500">From MoE Through Arada To Minilik High School Distribution Steps
                    </div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-700">
                        <div class=" rounded-full bg-blue-500 w-10 h-10 flex items-center justify-center">
                            <span class="text-sm text-blue-50 font-bold">4</span>
                        </div>
                    </div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <x-button btnType="success" class="py-1">Active</x-button>
                </td>

                <td class="px-5 py-2">
                    <x-action.table-button id="{{ $record }}" view="viewBook" edit="editBook" delete="deleteBook" />
                </td>
            </x-data-table.tr>

            <x-data-table.tr>
                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-700">3</div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-600 font-semibold">Bishoftu Priparatory School Book Distribution</div>
                </td>

                <td class="px-5 py-2 flex-wrap">
                    <div class="text-sm text-gray-500">From MoE to Oromia Region East Shoa Zone to Adaa Worda all the
                        way to Bishoftu Priparatory School</div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-700">
                        <div class=" rounded-full bg-gray-500 w-10 h-10 flex items-center justify-center">
                            <span class="text-sm text-blue-50 font-bold">6</span>
                        </div>
                    </div>
                </td>


                <td class="px-5 py-2 whitespace-nowrap">
                    <x-button btnType="danger" class="py-1">InActive</x-button>
                </td>

                <td class="px-5 py-2">
                    <x-action.table-button id="{{ $record }}" view="viewBook" edit="editBook" delete="deleteBook" />
                </td>
            </x-data-table.tr>
            {{-- @empty --}}
            {{-- <x-data-table.empty colspan=6 /> --}}
            {{-- @endforelse --}}
        </x-slot>
    </x-form.table>
</div>