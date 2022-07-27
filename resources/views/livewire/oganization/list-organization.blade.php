<div>
    <x-form.table title="Organizations List">
        <x-slot name="tableHeaders">
            <x-data-table.th scope="col">#</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Organizations') }}</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Location') }}</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Contact Person') }}</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Type') }}</x-data-table.th>
            <x-data-table.th scope="col" class="sr-only">{{__('Action') }}</x-data-table.th>
        </x-slot>

        <x-slot name="tableRows">
            @php $i = 1; @endphp
            @forelse($organizations as $record)
            <x-data-table.tr>
                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-700">{{$i++}}</div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 h-16 w-16">
                            <img class="h-16 w-16 rounded-full"
                                src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=4&w=256&h=256&q=60"
                                alt="">
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-medium text-gray-700">
                                Name
                            </div>
                            <div class="text-sm text-gray-500">
                                jane.cooper@example.com
                            </div>
                            <div class="text-sm text-gray-500">
                                0987654321
                            </div>
                        </div>
                    </div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="flex items-center">
                        <div class="text-xs text-gray-500">
                            <div class="">
                                Country
                            </div>
                            <div class="">
                                Region / City
                            </div>
                            <div class="">
                                Zone / Sub-city
                            </div>
                        </div>
                        <div class="ml-4 text-sm font-medium text-gray-700">
                            <div class="">
                                Ethiopia
                            </div>
                            <div class="">
                                Addis Ababa
                            </div>
                            <div class="">
                                Yeka
                            </div>
                        </div>
                    </div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="flex items-center">
                        <div class="text-xs text-gray-500">
                            <div class="">
                                Name
                            </div>
                            <div class="">
                                Email
                            </div>
                            <div class="">
                                Phone
                            </div>
                        </div>
                        <div class="ml-4 text-sm font-medium text-gray-700">
                            <div class="">
                                Jane Cooper
                            </div>
                            <div class="">
                                jane.cooper@example.com
                            </div>
                            <div class="">
                                0987654321
                            </div>
                        </div>
                    </div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-700">
                        Org Type
                    </div>
                </td>

                <td class="px-5 py-2">
                    <x-action.table-button id="{{ $record->id }}" edit="editKebele" delete="deleteKebele" />
                </td>
            </x-data-table.tr>
            @empty
            <x-data-table.empty colspan=4 />
            @endforelse
        </x-slot>
    </x-form.table>
</div>