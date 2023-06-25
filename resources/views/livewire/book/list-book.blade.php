<div>
    <x-form.table title="Books List">
        <x-slot name="tableHeaders">
            <x-data-table.th scope="col">#</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Book') }}</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Book Standards') }}</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Book File') }}</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Copies') }}</x-data-table.th>
            <x-data-table.th scope="col" class="sr-only">{{__('Action') }}</x-data-table.th>
        </x-slot>

        <x-slot name="tableRows">
            @php $i = 1; $record = 1;@endphp
            {{-- @forelse($books as $record) --}}

            @foreach ($books as $book)
            <x-data-table.tr>
                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-700 dark:text-gray-100">{{ $i++ }}</div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <x-book.book-info image="{{$book->front_cover_location }}" grade="{{ $book->grade->name }}"
                        subject="{{ $book->subject->name }}" type="Student Text Book"
                        edition="Edition {{ $book->edition }}" ISBN="{{ $book->isbn }}" />
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <x-book.book-standards font="Noto Sans Ethiopics" print="Color Print (RGB)" paper="A5" />
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <x-book.book-file name="GRADE {{ $book->grade->name }}  {{ $book->subject->name }}" type="PDF"
                        size="15MB" />
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-md font-semibold text-gray-500 dark:text-gray-300">
                        --
                    </div>
                </td>

                <td class="px-5 py-2">
                    <x-action.table-button id="{{ $record }}" view="viewBook" edit="editBook" delete="deleteBook" />
                </td>
            </x-data-table.tr>
            @endforeach




            {{-- @empty --}}
            {{-- <x-data-table.empty colspan=6 /> --}}
            {{-- @endforelse --}}
        </x-slot>
    </x-form.table>
</div>