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
                    <div class="text-sm text-gray-700 dark:text-gray-100">{{$i++}}</div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <x-organization.info image="logom.png" name="Ministry of Education" email="mail@ministry.com"
                        phone="0987654321" />
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <x-organization.location country="Ethiopia" region="Addis Ababa" zone="Arada" />
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <x-organization.contact name="Abebe Kebede" email="abe@kebede.com" phone="0987654312" />
                </td>

                <td class=" px-5 py-2 whitespace-nowrap">
                    <div class="text-sm font-semibold text-gray-500">
                        Organization
                    </div>
                </td>

                <td class="px-5 py-2">
                    <x-action.table-button id="{{ $record->id }}" edit="editKebele" delete="deleteKebele" />
                </td>
            </x-data-table.tr>
            @empty
            <x-data-table.empty colspan=6 />
            @endforelse
        </x-slot>
    </x-form.table>
</div>