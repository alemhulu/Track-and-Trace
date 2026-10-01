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
            @php
                $totalCopies = (int) ($book->print_order_sum_no_of_books ?? 0);
                $frontCoverImage = ! empty($book->front_cover_location) ? $book->front_cover_location : 'https://via.placeholder.com/320x420?text=No+Front+Cover';
                $backCoverImage = ! empty($book->back_cover_location) ? $book->back_cover_location : 'https://via.placeholder.com/320x420?text=No+Back+Cover';
            @endphp
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
                        {{ number_format($totalCopies) }}
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
                            <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-gradient-to-r from-slate-50 to-white dark:from-gray-900 dark:to-gray-800 p-4 sm:p-5 mb-6">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white">
                                            Grade {{ $book->grade->name }} — {{ $book->subject->name }}
                                        </h3>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                            {{ $book->book_type ? 'Teacher Guide' : 'Student Text Book' }}
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <div class="text-right">
                                            <div class="text-xs text-gray-500 dark:text-gray-400">Copies Available</div>
                                            <div class="mt-1 text-2xl font-bold {{ $totalCopies > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400' }}">
                                                {{ number_format($totalCopies) }}
                                            </div>
                                        </div>
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $totalCopies > 0 ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300' }}">
                                            {{ $totalCopies > 0 ? 'Available' : 'Not Available' }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                                <div class="lg:col-span-1" x-data="{ activeCover: 'front' }">
                                    <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm">
                                        <div class="px-3 py-2 border-b border-gray-200 dark:border-gray-700">
                                            <div class="inline-flex rounded-lg bg-gray-100 dark:bg-gray-900 p-1 w-full">
                                                <button type="button"
                                                    @click="activeCover = 'front'"
                                                    :class="activeCover === 'front'
                                                        ? 'bg-white dark:bg-gray-700 text-blue-600 dark:text-blue-300 shadow-sm'
                                                        : 'text-gray-500 dark:text-gray-300'"
                                                    class="flex-1 px-3 py-1.5 text-xs font-semibold rounded-md transition">
                                                    Front Cover
                                                </button>
                                                <button type="button"
                                                    @click="activeCover = 'back'"
                                                    :class="activeCover === 'back'
                                                        ? 'bg-white dark:bg-gray-700 text-blue-600 dark:text-blue-300 shadow-sm'
                                                        : 'text-gray-500 dark:text-gray-300'"
                                                    class="flex-1 px-3 py-1.5 text-xs font-semibold rounded-md transition">
                                                    Back Cover
                                                </button>
                                            </div>
                                        </div>

                                        <img x-show="activeCover === 'front'" x-cloak src="{{ $frontCoverImage }}" alt="Front book cover"
                                            class="w-full h-80 object-cover">
                                        <img x-show="activeCover === 'back'" x-cloak src="{{ $backCoverImage }}" alt="Back book cover"
                                            class="w-full h-80 object-cover">

                                        <div class="px-3 py-2 text-xs text-gray-500 dark:text-gray-400 border-t border-gray-200 dark:border-gray-700">
                                            <span x-show="activeCover === 'front'" x-cloak>Showing front cover</span>
                                            <span x-show="activeCover === 'back'" x-cloak>Showing back cover</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="lg:col-span-2 space-y-4">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                                        <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-3 bg-gray-50 dark:bg-gray-900/40">
                                            <div class="text-xs text-gray-500 dark:text-gray-400">Edition</div>
                                            <div class="font-semibold text-gray-800 dark:text-gray-100">{{ $book->edition ?? '---' }}</div>
                                        </div>
                                        <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-3 bg-gray-50 dark:bg-gray-900/40">
                                            <div class="text-xs text-gray-500 dark:text-gray-400">Volume</div>
                                            <div class="font-semibold text-gray-800 dark:text-gray-100">{{ $book->volume ?? '---' }}</div>
                                        </div>
                                        <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-3 bg-gray-50 dark:bg-gray-900/40">
                                            <div class="text-xs text-gray-500 dark:text-gray-400">ISBN</div>
                                            <div class="font-semibold text-gray-800 dark:text-gray-100">{{ $book->isbn ?? '---' }}</div>
                                        </div>
                                        <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-3 bg-gray-50 dark:bg-gray-900/40">
                                            <div class="text-xs text-gray-500 dark:text-gray-400">Print Orders</div>
                                            <div class="font-semibold text-gray-800 dark:text-gray-100">{{ number_format($book->print_order_count) }}</div>
                                        </div>
                                        <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-3 bg-gray-50 dark:bg-gray-900/40">
                                            <div class="text-xs text-gray-500 dark:text-gray-400">Print Type</div>
                                            <div class="font-semibold text-gray-800 dark:text-gray-100">{{ $book->print_type ?? '---' }}</div>
                                        </div>
                                        <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-3 bg-gray-50 dark:bg-gray-900/40">
                                            <div class="text-xs text-gray-500 dark:text-gray-400">Paper Size</div>
                                            <div class="font-semibold text-gray-800 dark:text-gray-100">{{ $book->paper_size ?? '---' }}</div>
                                        </div>
                                        <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-3 bg-gray-50 dark:bg-gray-900/40 sm:col-span-2">
                                            <div class="text-xs text-gray-500 dark:text-gray-400">Created</div>
                                            <div class="font-semibold text-gray-800 dark:text-gray-100">{{ $book->created_at?->format('M d, Y') ?? '---' }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if (! empty($book->file_location))
                            <div class="pt-4">
                                <a href="{{ $book->file_location }}" target="_blank"
                                    class="inline-flex items-center px-4 py-2 rounded-md bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium">
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
