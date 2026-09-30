<div>
    <x-stat.section name="Warehouse Info" col=3>
        <x-stat.list value="{{ number_format($totalWarehouses) }}" text="Total Warehouse"></x-stat.list>
        <x-stat.list value="{{ number_format($totalStores) }}" text="Total Stores"></x-stat.list>
        <x-stat.list value="{{ number_format($totalBooksInStores) }}" text="Total Books In Stores"></x-stat.list>
    </x-stat.section>

    <x-form.table title="Wearhouse List">
        <x-slot name="tableHeaders">
            <x-data-table.th scope="col"> {{__('Name') }}</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Description') }}</x-data-table.th>
            {{-- <x-data-table.th scope="col"> {{__('Organization') }}</x-data-table.th> --}}
            <x-data-table.th scope="col"> {{__('Conact Person') }}</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Books In Wearhouse') }}</x-data-table.th>
            <x-data-table.th scope="col" class="sr-only">{{__('Action') }}</x-data-table.th>
        </x-slot>

        <x-slot name="tableRows">
            @php $i = 1; $record = 1;@endphp
            @forelse($wearehouses as $record)
            <x-data-table.tr>

                <td class="px-5 py-2 whitespace-nowrap">
                    <div
                        class=" bg-sky-800 flex flex-col text-md font-semibold text-gray-100 dark:text-gray-200 rounded-lg border p-2">
                        <span class="font-bold flex w-full justify-between">Store <span
                                class="text-xs rounded bg-blue-500 px-2 flex items-center">{{ optional(optional($record->organization)->organizationType)->name ?? 'N/A' }}</span></span>
                        <span class="text-gray-200 text-xs">{{ optional($record->organization)->name ?? 'N/A' }}</span>
                    </div>
                </td>

                {{-- <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-700 dark:text-gray-200 whitespace-pre-line">Ministry of Education
                        Wearhouse located at
                        around CMC</div>
                </td> --}}

                <td class="px-5 py-2 whitespace-nowrap">
                    <x-organization.info image="logom.png" name="{{ optional($record->organization)->name ?? 'N/A' }}"
                        email="{{ optional($record->organization)->email ?? 'email: ---' }}"
                        phone="{{ optional($record->organization)->phone ?? 'phone: ---' }}" />
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <x-organization.contact name="{{ $record->user->name ?? '---'}}"
                        email="{{ $record->user->email ?? '---'}}" phone="{{ $record->user->phone ?? '---'}}" />
                </td>

                {{-- <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-lg text-gray-600 font-semibold dark:text-gray-300">5</div>
                </td> --}}

                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-lg text-gray-500 font-semibold dark:text-gray-300">
                        {{ number_format($record->packages_count) }}</div>
                </td>

                <td class="px-5 py-2">
                    <x-action.table-button id="{{ $record->id }}" view="viewWarehouse" edit="editWarehouse"
                        delete="deleteWarehouse" />
                </td>
            </x-data-table.tr>
            @empty
            <x-data-table.empty colspan=6 />
            @endforelse
        </x-slot>

        {{ $wearehouses->links() }}
    </x-form.table>

</div>
