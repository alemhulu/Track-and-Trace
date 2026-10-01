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
            @php $i = 1; @endphp
            @forelse ($books as $book)
            <x-data-table.tr>
                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-700 dark:text-gray-100">{{ $i++ }}</div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <x-book.book-info image="{{ $book->front_cover_location }}" grade="{{ $book->grade->name }}"
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
                    <div class="font-semibold text-gray-500 text-md dark:text-gray-300">
                        {{ number_format($book->print_order_count) }}
                    </div>
                </td>

                <td class="px-5 py-2">
                    <x-action.table-button id="{{ $book->id }}" view="#viewBook{{ $book->id }}" edit="editBook"
                        delete="deleteBook" link />

                    <x-data-table.modal name="viewBook{{ $book->id }}" maxWidth="3xl" :buttons="false">
                        <x-slot name="title">
                            Book Detail
                        </x-slot>
                        <x-slot name="body">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                <div>
                                    <img src="{{ $book->front_cover_location }}" alt="Book cover"
                                        class="w-full h-72 object-cover rounded-lg border">
                                </div>
                                <div class="space-y-2 text-sm text-gray-700 dark:text-gray-200">
                                    <div><span class="font-semibold">Grade:</span> {{ $book->grade->name }}</div>
                                    <div><span class="font-semibold">Subject:</span> {{ $book->subject->name }}</div>
                                    <div><span class="font-semibold">Book Type:</span>
                                        {{ $book->book_type ? 'Teacher Guide' : 'Student Text Book' }}
                                    </div>
                                    <div><span class="font-semibold">Edition:</span> {{ $book->edition }}</div>
                                    <div><span class="font-semibold">Volume:</span> {{ $book->volume ?? '---' }}</div>
                                    <div><span class="font-semibold">ISBN:</span> {{ $book->isbn }}</div>
                                    <div><span class="font-semibold">Print Type:</span> {{ $book->print_type ?? '---' }}</div>
                                    <div><span class="font-semibold">Paper Size:</span> {{ $book->paper_size ?? '---' }}</div>
                                    <div><span class="font-semibold">Created:</span> {{ $book->created_at?->format('M d, Y') ?? '---' }}</div>
                                    <div><span class="font-semibold">Print Orders:</span> {{ number_format($book->print_order_count) }}</div>
                                </div>
                            </div>

                            @if (! empty($book->file_location))
                            <div class="pt-2">
                                <a href="{{ $book->file_location }}" target="_blank"
                                    class="text-blue-600 dark:text-blue-300 underline">
                                    Open Book File
                                </a>
                            </div>
                            @endif
                        </x-slot>
                    </x-data-table.modal>
                </td>
            </x-data-table.tr>
            @empty
            <x-data-table.empty colspan=6 />
            @endforelse
        </x-slot>

        {{ $books->links() }}
    </x-form.table>
</div>
