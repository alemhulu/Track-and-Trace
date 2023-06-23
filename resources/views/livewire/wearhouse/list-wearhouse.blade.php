<div>
    <x-stat.section name="Warehouse Info" col=3>
        <x-stat.list value="700" text="Total Warehouse"></x-stat.list>
        <x-stat.list value="2.4K" text="Total Stores"></x-stat.list>
        <x-stat.list value="2.1M" text="Total Books In Stores"></x-stat.list>
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
                                class="text-xs rounded bg-blue-500 px-2 flex items-center">{{ $record->organization->organizationType->name }}</span></span>
                        <span class="text-gray-200 text-xs"> {{ $record->organization->name }}</span>
                    </div>
                </td>

                {{-- <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-700 dark:text-gray-200 whitespace-pre-line">Ministry of Education
                        Wearhouse located at
                        around CMC</div>
                </td> --}}

                <td class="px-5 py-2 whitespace-nowrap">
                    <x-organization.info image="logom.png" name="{{ $record->organization->name }}"
                        email="{{ $record->organization->email ?? 'emial: ---' }}"
                        phone="{{ $record->organization->phone ?? 'phone: ---' }}" />
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <x-organization.contact name="{{ $record->user->name ?? '---'}}"
                        email="{{ $record->user->email ?? '---'}}" phone="{{ $record->user->phone ?? '---'}}" />
                </td>

                {{-- <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-lg text-gray-600 font-semibold dark:text-gray-300">5</div>
                </td> --}}

                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-lg text-gray-500 font-semibold dark:text-gray-300">000</div>
                </td>

                <td class="px-5 py-2">
                    <x-action.table-button id="{{ $record }}" view="viewBook" edit="editBook" delete="deleteBook" />
                </td>
            </x-data-table.tr>
            @empty
            <x-data-table.empty colspan=6 />
            @endforelse
        </x-slot>

        {{ $wearehouses->links() }}
    </x-form.table>

</div>