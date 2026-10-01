<div>
    <div class="mb-3 flex justify-end">
        <a href="{{ route('manual-tracking.books.add') }}"
            class="inline-flex items-center px-4 py-2 rounded-md bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium">
            Add Manual Book
        </a>
    </div>

    <x-form.table title="Manual Books">
        <x-slot name="tableHeaders">
            <x-data-table.th scope="col">#</x-data-table.th>
            <x-data-table.th scope="col">{{ __('Title') }}</x-data-table.th>
            <x-data-table.th scope="col">{{ __('Book Detail') }}</x-data-table.th>
            <x-data-table.th scope="col">{{ __('Copies') }}</x-data-table.th>
            <x-data-table.th scope="col">{{ __('Packages') }}</x-data-table.th>
            <x-data-table.th scope="col" class="sr-only">{{ __('Action') }}</x-data-table.th>
        </x-slot>

        <x-slot name="tableRows">
            @forelse ($books as $book)
            <x-data-table.tr>
                <td class="px-5 py-2 whitespace-nowrap text-sm text-gray-700 dark:text-gray-100">{{ $loop->iteration }}
                </td>
                <td class="px-5 py-2 whitespace-nowrap text-sm text-gray-700 dark:text-gray-100">{{ $book->title }}</td>
                <td class="px-5 py-2 whitespace-nowrap text-sm text-gray-700 dark:text-gray-100">
                    <div>{{ $book->grade_name ?: '-' }} / {{ $book->subject_name ?: '-' }}</div>
                    <div class="text-xs text-gray-400">ISBN: {{ $book->isbn ?: '-' }}</div>
                </td>
                <td class="px-5 py-2 whitespace-nowrap text-sm font-semibold text-gray-700 dark:text-gray-100">
                    {{ number_format($book->total_copies) }}</td>
                <td class="px-5 py-2 whitespace-nowrap text-sm text-gray-700 dark:text-gray-100">
                    {{ number_format($book->packages_count) }}</td>
                <td class="px-5 py-2">
                    <x-action.table-button id="{{ $book->id }}" edit="editBook" delete="deleteBook" link />
                </td>
            </x-data-table.tr>
            @empty
            <x-data-table.empty colspan="6" />
            @endforelse
        </x-slot>

        {{ $books->links() }}
    </x-form.table>
</div>
