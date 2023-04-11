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
                @php $i = 1; $record = 1;@endphp
                {{-- @forelse($books as $record) --}}
                <x-data-table.tr>
                    <td class="px-5 py-2 whitespace-nowrap">
                        <div class="text-sm text-gray-700 dark:text-gray-100">1</div>
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

                <x-data-table.tr>
                    <td class="px-5 py-2 whitespace-nowrap">
                        <div class="text-sm text-gray-700 dark:text-gray-100">2</div>
                    </td>

                    <td class="px-5 py-2 whitespace-nowrap">
                        <x-book.book-info
                            image="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTO4PF0UBFIlAShsDV_dJAqaXPUMuSHLAT-bcp6nyF52wSILxHhmiXOjB2ZYb8Vri76pbI&usqp=CAU"
                            grade="Grade 12" subject="Physics" type="Student Text Book" edition="2nd Edition 2011"
                            ISBN="2387548" />
                    </td>

                    <td class="px-5 py-2 whitespace-nowrap">
                        <x-book.print-info batch="BTSC9876458" copies="2600 + 65" packages="65" />
                    </td>

                    <td class="px-5 py-2 whitespace-nowrap">
                        <x-organization.info
                            image="data:image/jpeg;base64,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"
                            name="Bernna Selam Printing" email="mail@bernnaselam.com" phone="0987654321" />
                    </td>

                    <td class="px-5 py-2 whitespace-nowrap">
                        <x-button btnType="info">Accepetd</x-button>
                    </td>

                    <td class="px-5 py-2">
                        <x-action.table-button id="{{ $record }}" view="viewBook" edit="editBook" delete="deleteBook" />
                    </td>
                </x-data-table.tr>

                <x-data-table.tr>
                    <td class="px-5 py-2 whitespace-nowrap">
                        <div class="text-sm text-gray-700 dark:text-gray-100">1</div>
                    </td>

                    <td class="px-5 py-2 whitespace-nowrap">
                        <x-book.book-info
                            image="https://i.gr-assets.com/images/S/compressed.photo.goodreads.com/books/1554186848i/44768375._UY844_SS844_.jpg"
                            grade="Grade 11" subject="Mathematics" type="Student Text Book" edition="1st Edition 2010"
                            ISBN="5732482" />
                    </td>

                    <td class="px-5 py-2 whitespace-nowrap">
                        <x-book.print-info batch="BTSC12634523971" copies="520 + 13" packages="13" />
                    </td>

                    <td class="px-5 py-2 whitespace-nowrap">
                        <x-organization.info
                            image="https://i0.wp.com/asternega.com/wp-content/uploads/2018/12/cropped-aster-logo-1.png?fit=512%2C512&ssl=1"
                            name="Aster Nega" email="info@asternega.com" phone="0987654321" />
                    </td>

                    <td class="px-5 py-2 whitespace-nowrap">
                        <x-button btnType="success">Printed</x-button>
                    </td>

                    <td class="px-5 py-2">
                        <x-action.table-button id="{{ $record }}" view="viewBook" edit="editBook" delete="deleteBook" />
                    </td>
                </x-data-table.tr>

                <x-data-table.tr>
                    <td class="px-5 py-2 whitespace-nowrap">
                        <div class="text-sm text-gray-700 dark:text-gray-100">1</div>
                    </td>

                    <td class="px-5 py-2 whitespace-nowrap">
                        <x-book.book-info
                            image="https://i.gr-assets.com/images/S/compressed.photo.goodreads.com/books/1554186690i/44768361._UY795_SS795_.jpg"
                            grade="Grade 9" subject="Chemistry" type="Student Text Book" edition="1st Edition 2007"
                            ISBN="5678644" />
                    </td>

                    <td class="px-5 py-2 whitespace-nowrap">
                        <x-book.print-info batch="BTSC12634523971" copies="5200 + 130" packages="130" />
                    </td>

                    <td class="px-5 py-2 whitespace-nowrap">
                        <x-organization.info
                            image="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQAEhLe6TR98dtIOnlrVA92__zisDYRj5MS0Q&usqp=CAU"
                            name="Mega Printing PLC" email="info@megaprint.com" phone="0987654321" />
                    </td>

                    <td class="px-5 py-2 whitespace-nowrap">
                        <x-button btnType="danger">Rejected</x-button>
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
</div>