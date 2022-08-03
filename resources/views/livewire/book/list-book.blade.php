<div>
    <x-form.table title="Organizations List">
        <x-slot name="tableHeaders">
            <x-data-table.th scope="col">#</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Book') }}</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Book Standards') }}</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Book File') }}</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Created At') }}</x-data-table.th>
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
                    <x-book.book-info image="https://i.ytimg.com/vi/NNKPR6nICJI/maxresdefault.jpg" grade="Grade 10"
                        subject="Biology" type="Student Text Book" edition="1st Edition 2013" ISBN="4820715" />
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <x-book.book-standards font="Noto Sans Ethiopics" print="Color Print (RGB)" paper="A5" />
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <x-book.book-file name="Grade-10-Student-Text-Book-Biology" type="PDF" size="15MB" />
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-md font-semibold text-gray-500">
                        Aug 14, 2013
                    </div>
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
                    <x-book.book-info
                        image="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTO4PF0UBFIlAShsDV_dJAqaXPUMuSHLAT-bcp6nyF52wSILxHhmiXOjB2ZYb8Vri76pbI&usqp=CAU"
                        grade="Grade 12" subject="Physics" type="Student Text Book" edition="1st Edition 2011"
                        ISBN="4820715" />
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <x-book.book-standards font="Noto Sans Ethiopics" print="Color Print (RGB)" paper="A5" />
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <x-book.book-file name="Grade-12-Student-Text-Book-Physics" type="PDF" size="17MB" />
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-md font-semibold text-gray-500">
                        Jun 27, 2013
                    </div>
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