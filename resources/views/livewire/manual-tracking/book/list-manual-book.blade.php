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
            <x-data-table.th scope="col">{{ __('Allocated') }}</x-data-table.th>
            <x-data-table.th scope="col" class="th-wrap">{{ __('Student Books on Hand') }}</x-data-table.th>
            <x-data-table.th scope="col" class="th-wrap">{{ __('Teacher Guides on Hand') }}</x-data-table.th>
            <x-data-table.th scope="col"><span class="sr-only">{{ __('Action') }}</span></x-data-table.th>
        </x-slot>

        <x-slot name="tableRows">
            @forelse ($books as $book)
            <x-data-table.tr>
                <td class="px-5 py-2 whitespace-nowrap text-sm text-gray-700 dark:text-gray-100">
                    {{ $books->firstItem() + $loop->index }}
                </td>
                <td class="px-5 py-2 text-sm text-gray-700 dark:text-gray-100">
                    <div class="font-semibold cell-wrap">{{ $book->title }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">
                        {{ $book->region->name ?? 'No region' }} · {{ $book->grade_name ?: '-' }} · {{ $book->subject_name ?: '-' }}{{ $book->language ? ' · ' . $book->language : '' }}
                    </div>
                </td>
                <td class="px-5 py-2 text-sm text-gray-700 dark:text-gray-100">
                    <div class="cell-wrap">{{ $book->students_per_package }} per package{{ $book->has_teacher_guide ? ' + 1 teacher guide' : ' (no teacher guide)' }}</div>
                </td>
                <td class="px-5 py-2 text-xs text-gray-600 dark:text-gray-300">
                    @if ($book->codes->isEmpty())
                    <span class="text-amber-600">Not linked</span>
                    @else
                    <x-ui.clamp title="QR codes · {{ $book->title }}">
                        @include('livewire.manual-tracking.book.partials.codes', ['codes' => $book->codes])
                    </x-ui.clamp>
                    @endif
                </td>
                <td class="px-5 py-2 whitespace-nowrap text-sm text-gray-700 dark:text-gray-100">
                    @if ($book->student_allocated || $book->teacher_allocated)
                    {{ number_format((int) $book->student_allocated) }} SB
                    <span class="block text-xs text-gray-500 dark:text-gray-400">{{ number_format((int) $book->teacher_allocated) }} TG</span>
                    @else
                    <span class="text-xs text-gray-400">—</span>
                    @endif
                </td>
                <td class="px-5 py-2 whitespace-nowrap text-sm font-semibold text-gray-700 dark:text-gray-100">
                    {{ number_format((int) $book->student_on_hand) }}
                </td>
                <td class="px-5 py-2 whitespace-nowrap text-sm text-gray-700 dark:text-gray-100">
                    {{ $book->has_teacher_guide ? number_format((int) $book->teacher_on_hand) : '—' }}
                </td>
                <td class="px-5 py-2">
                    <div class="flex items-center gap-1.5">
                        <x-ui.view-modal title="{{ $book->title }}" label="View title details">
                            <dl class="grid grid-cols-2 gap-x-4 gap-y-3">
                                <div><dt class="text-xs text-gray-500">Region</dt><dd>{{ $book->region->name ?? 'All regions' }}</dd></div>
                                <div><dt class="text-xs text-gray-500">Grade · Subject</dt><dd>{{ $book->grade_name ?: '-' }} · {{ $book->subject_name ?: '-' }}</dd></div>
                                <div><dt class="text-xs text-gray-500">Packaging</dt><dd>{{ $book->students_per_package }} student books{{ $book->has_teacher_guide ? ' + 1 teacher guide' : '' }}</dd></div>
                                <div><dt class="text-xs text-gray-500">On hand (SB / TG)</dt><dd>{{ number_format((int) $book->student_on_hand) }} / {{ $book->has_teacher_guide ? number_format((int) $book->teacher_on_hand) : '—' }}</dd></div>
                                <div class="col-span-2">
                                    <dt class="text-xs text-gray-500">QR codes</dt>
                                    <dd class="mt-1 text-xs">
                                        @if ($book->codes->isEmpty())
                                        <span class="text-amber-600">Not linked</span>
                                        @else
                                        @include('livewire.manual-tracking.book.partials.codes', ['codes' => $book->codes])
                                        @endif
                                    </dd>
                                </div>
                            </dl>
                        </x-ui.view-modal>
                        @if ($canEdit)
                        <x-action.table-button id="{{ $book->id }}" edit="editBook" link />
                        @endif
                        @if ($canDelete)
                        <button type="button" class="icon-btn-delete" title="Delete title" aria-label="Delete title"
                            x-on:click="if (confirm(@js('Delete ' . $book->title . '? This cannot be undone.'))) $wire.deleteBook({{ $book->id }})">
                            <i class="flex fi fi-rr-trash"></i>
                        </button>
                        @endif
                    </div>
                </td>
            </x-data-table.tr>
            @empty
            <x-data-table.empty colspan="8" />
            @endforelse
        </x-slot>

        {{ $books->links() }}
    </x-form.table>
</div>
