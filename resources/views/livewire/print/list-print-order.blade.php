<div>
    <div>
        <x-form.table title="Organizations List">
            <x-slot name="tableHeaders">
                <x-data-table.th scope="col">#</x-data-table.th>
                <x-data-table.th scope="col"> {{__('Book') }}</x-data-table.th>
                <x-data-table.th scope="col"> {{__('Print Info') }}</x-data-table.th>
                <x-data-table.th scope="col"> {{__('Organization') }}</x-data-table.th>
                <x-data-table.th scope="col"> {{__('Order Status') }}</x-data-table.th>
                <x-data-table.th scope="col" class="sr-only">{{__('Action') }}</x-data-table.th>
            </x-slot>

            <x-slot name="tableRows">
                @forelse($orders as $key=>$record)
                <x-data-table.tr>
                    <td class="px-5 py-2 whitespace-nowrap">
                        <div class="text-sm text-gray-700 dark:text-gray-100">{{ $key +1 }}</div>
                    </td>

                    <td class="px-5 py-2 whitespace-nowrap">
                        <x-book.book-info image="/biology-grade-10.jpg" grade="Grade 10" subject="Biology"
                            type="Student Text Book" edition="1st Edition 2013" ISBN="4820715" />
                    </td>

                    <td class="px-5 py-2 whitespace-nowrap">
                        <x-book.print-info batch="BTSC12634523971" copies="520 + 13" packages="13" />
                    </td>

                    <td class="px-5 py-2 whitespace-nowrap">
                        <x-organization.info image="logom.png" name="Ministry of Education" email="mail@ministry.com"
                            phone="0987654321" />
                    </td>

                    <td class="px-5 py-2 whitespace-nowrap">
                        <x-button btnType="secondary">Ordered</x-button>
                    </td>

                    <td class="px-5 py-2">
                        <x-action.table-button id="{{ $record }}" view="viewBook" edit="editBook" delete="deleteBook" />
                    </td>
                </x-data-table.tr>
                @empty
                <x-data-table.empty colspan=6 />
                @endforelse
            </x-slot>
        </x-form.table>
    </div>
</div>