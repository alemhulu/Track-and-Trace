<div>
    <x-stat.section name="Warehouse Info" col=3>
        <x-stat.list value="700" text="Total Warehouse"></x-stat.list>
        <x-stat.list value="2.4K" text="Total Stores"></x-stat.list>
        <x-stat.list value="2.1M" text="Total Books In Stores"></x-stat.list>
    </x-stat.section>

    <x-form.table title="Wearhouse List">
        <x-slot name="tableHeaders">
            <x-data-table.th scope="col">#</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Name') }}</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Description') }}</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Organization') }}</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Conact Person') }}</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Stores') }}</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Books In Wearhouse') }}</x-data-table.th>
            <x-data-table.th scope="col" class="sr-only">{{__('Action') }}</x-data-table.th>
        </x-slot>

        <x-slot name="tableRows">
            @php $i = 1; $record = 1;@endphp
            {{-- @forelse($books as $record) --}}
            <x-data-table.tr>
                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-700 dark:text-gray-100">1</div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-md font-semibold text-gray-600 dark:text-gray-200">MoE Wearhouse 1</div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-700 dark:text-gray-200 whitespace-pre-line">Ministry of Education
                        Wearhouse located at
                        around CMC</div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <x-organization.info image="logom.png" name="Ministry of Education" email="mail@ministry.com"
                        phone="0987654321" />
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <x-organization.contact name="Abebe Kebede" email="abe@kebede.com" phone="0987654312" />
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-lg text-gray-600 font-semibold dark:text-gray-300">5</div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-lg text-gray-600 font-semibold dark:text-gray-300">200K+</div>
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