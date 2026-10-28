<div>
    @if ($canCreate)
    <div class="mb-3 flex justify-end">
        <a href="{{ route('manual-tracking.books.add') }}"
            class="btn-primary">
            Add Title
        </a>
    </div>
    @endif

    <x-form.table title="Titles">
        <x-slot name="tableHeaders">
            <x-data-table.th scope="col">#</x-data-table.th>
            <x-data-table.th scope="col">{{ __('Title') }}</x-data-table.th>
            <x-data-table.th scope="col">{{ __('Packaging') }}</x-data-table.th>
            <x-data-table.th scope="col">{{ __('QR Codes') }}</x-data-table.th>
            <x-data-table.th scope="col">{{ __('Student Books on Hand') }}</x-data-table.th>
            <x-data-table.th scope="col">{{ __('Teacher Guides on Hand') }}</x-data-table.th>
            <x-data-table.th scope="col" class="sr-only">{{ __('Action') }}</x-data-table.th>
        </x-slot>

        <x-slot name="tableRows">
            @forelse ($books as $book)
            <x-data-table.tr>
                <td class="px-5 py-2 whitespace-nowrap text-sm text-gray-700 dark:text-gray-100">
                    {{ $books->firstItem() + $loop->index }}
                </td>
                <td class="px-5 py-2 whitespace-nowrap text-sm text-gray-700 dark:text-gray-100">
                    <div class="font-semibold">{{ $book->title }}</div>
                    <div class="text-xs text-gray-400">
                        {{ $book->region->name ?? 'No region' }} · {{ $book->grade_name ?: '-' }} · {{ $book->subject_name ?: '-' }}
                    </div>
                </td>
                <td class="px-5 py-2 whitespace-nowrap text-sm text-gray-700 dark:text-gray-100">
                    {{ $book->students_per_package }}{{ $book->has_teacher_guide ? ' + 1 teacher guide' : ' (no teacher guide)' }}
                </td>
                <td class="px-5 py-2 text-xs text-gray-600 dark:text-gray-300">
                    @forelse ($book->codes as $code)
                    <div>
                        <span class="font-medium">{{ \App\Models\ManualTracking\ManualBook::EDITIONS[$code->edition] ?? $code->edition }}:</span>
                        {{ $code->payload }}{{ $code->print_code ? ' · ' . $code->print_code : '' }}
                    </div>
                    @empty
                    <span class="text-amber-600">Not linked</span>
                    @endforelse
                </td>
                <td class="px-5 py-2 whitespace-nowrap text-sm font-semibold text-gray-700 dark:text-gray-100">
                    {{ number_format((int) $book->student_on_hand) }}
                </td>
                <td class="px-5 py-2 whitespace-nowrap text-sm text-gray-700 dark:text-gray-100">
                    {{ $book->has_teacher_guide ? number_format((int) $book->teacher_on_hand) : '—' }}
                </td>
                <td class="px-5 py-2">
                    @if ($canEdit || $canDelete)
                    <x-action.table-button id="{{ $book->id }}" :edit="$canEdit ? 'editBook' : null" :delete="$canDelete ? 'deleteBook' : null" link />
                    @endif
                </td>
            </x-data-table.tr>
            @empty
            <x-data-table.empty colspan="7" />
            @endforelse
        </x-slot>

        {{ $books->links() }}
    </x-form.table>
</div>
