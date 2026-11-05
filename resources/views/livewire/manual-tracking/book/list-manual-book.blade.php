<div class="space-y-4">
    @php
        $percentOf = fn ($part, $whole) => $whole > 0 ? round($part / $whole * 100, 1) : 0;
    @endphp

    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <x-ui.stat label="Titles" :value="number_format($stats['titles'])" icon="fi-rr-book">
            {{ number_format($stats['grades']) }} grades · {{ number_format($stats['languages']) }} languages{{ $filtersActive ? ' · filtered' : '' }}
        </x-ui.stat>
        <x-ui.stat label="Allocated {{ $year ? \App\Support\AcademicYear::label((int) $year) : '' }}" :value="number_format($stats['allocated_titles'])"
            icon="fi-rr-target" tone="info" :percent="$percentOf($stats['allocated_titles'], $stats['titles'])">
            {{ number_format($stats['student_allocated']) }} SB · {{ number_format($stats['teacher_allocated']) }} TG allocated
        </x-ui.stat>
        <x-ui.stat label="Student books on hand" :value="number_format($stats['student_on_hand'])" icon="fi-rr-graduation-cap" tone="success">
            {{ number_format($stats['student_moving']) }} more on the road · {{ number_format($stats['in_stock']) }} titles in stock
        </x-ui.stat>
        <x-ui.stat label="Teacher guides on hand" :value="number_format($stats['teacher_on_hand'])" icon="fi-rr-book-alt" tone="success">
            {{ number_format($stats['teacher_moving']) }} more on the road · {{ number_format($stats['with_teacher_guide']) }} titles have a TG
        </x-ui.stat>
    </div>

    <div class="grid grid-cols-2 gap-3 xl:grid-cols-4">
        <x-ui.stat label="QR codes linked" :value="number_format($stats['linked'])" tone="brand" icon="fi-rr-shield-check"
            :percent="$percentOf($stats['linked'], $stats['titles'])" :active="$filters['codes'] === 'linked'" wire:click="setFilter('codes', 'linked')">
            {{ $percentOf($stats['linked'], $stats['titles']) }}% of titles
        </x-ui.stat>
        <x-ui.stat label="No QR codes" :value="number_format($stats['titles'] - $stats['linked'])" tone="warning" icon="fi-rr-exclamation"
            :active="$filters['codes'] === 'unlinked'" wire:click="setFilter('codes', 'unlinked')">
            Packages of these titles cannot be scanned
        </x-ui.stat>
        <x-ui.stat label="Not allocated" :value="number_format($stats['titles'] - $stats['allocated_titles'])" tone="neutral" icon="fi-rr-inbox"
            :active="$filters['allocation'] === 'unallocated'" wire:click="setFilter('allocation', 'unallocated')">
            Not in the {{ $year ? \App\Support\AcademicYear::label((int) $year) : 'latest' }} allocation
        </x-ui.stat>
        <x-ui.stat label="Out of stock" :value="number_format($stats['titles'] - $stats['in_stock'])" tone="danger" icon="fi-rr-box"
            :active="$filters['stock'] === 'none'" wire:click="setFilter('stock', 'none')">
            No copies on hand anywhere
        </x-ui.stat>
    </div>

    <div class="p-4 card">
        <div class="grid grid-cols-2 gap-3 md:grid-cols-4 xl:grid-cols-8">
            <div>
                <x-jet-label for="filter-region" value="Region" />
                <x-form.select wire:model="filters.region" id="filter-region">
                    <option value="">Any region</option>
                    <option value="all">Used in every region</option>
                    @foreach ($regions as $region)
                    <option value="{{ $region->id }}">{{ $region->name }}</option>
                    @endforeach
                </x-form.select>
            </div>
            <div>
                <x-jet-label for="filter-grade" value="Grade" />
                <x-form.select wire:model="filters.grade" id="filter-grade">
                    <option value="">All grades</option>
                    @foreach ($grades as $option)
                    <option value="{{ $option }}">{{ $option }}</option>
                    @endforeach
                </x-form.select>
            </div>
            <div>
                <x-jet-label for="filter-subject" value="Subject" />
                <x-form.select wire:model="filters.subject" id="filter-subject">
                    <option value="">All subjects</option>
                    @foreach ($subjects as $option)
                    <option value="{{ $option }}">{{ $option }}</option>
                    @endforeach
                </x-form.select>
            </div>
            <div>
                <x-jet-label for="filter-language" value="Language" />
                <x-form.select wire:model="filters.language" id="filter-language">
                    <option value="">All languages</option>
                    @foreach ($languages as $option)
                    <option value="{{ $option }}">{{ $option }}</option>
                    @endforeach
                </x-form.select>
            </div>
            <div>
                <x-jet-label for="filter-tg" value="Teacher guide" />
                <x-form.select wire:model="filters.teacher_guide" id="filter-tg">
                    <option value="">Any</option>
                    <option value="with">With TG</option>
                    <option value="without">Without TG</option>
                </x-form.select>
            </div>
            <div>
                <x-jet-label for="filter-codes" value="QR codes" />
                <x-form.select wire:model="filters.codes" id="filter-codes">
                    <option value="">Any</option>
                    <option value="linked">Linked</option>
                    <option value="unlinked">Not linked</option>
                </x-form.select>
            </div>
            <div>
                <x-jet-label for="filter-stock" value="Stock" />
                <x-form.select wire:model="filters.stock" id="filter-stock">
                    <option value="">Any</option>
                    <option value="in_stock">In stock</option>
                    <option value="none">Out of stock</option>
                </x-form.select>
            </div>
            <div>
                <x-jet-label for="filter-allocation" value="Allocation" />
                <x-form.select wire:model="filters.allocation" id="filter-allocation">
                    <option value="">Any</option>
                    <option value="allocated">Allocated</option>
                    <option value="unallocated">Not allocated</option>
                </x-form.select>
            </div>
        </div>
        <div class="flex flex-wrap items-center justify-between gap-2 mt-3">
            <p class="text-xs text-gray-500 dark:text-gray-400">Tiles above with a filter apply it on click; click again to clear.</p>
            <div class="flex items-center gap-3">
                @if ($filtersActive)
                <button type="button" wire:click="clearFilters" class="text-sm link">Clear filters</button>
                @endif
                @if ($canCreate)
                <a href="{{ route('manual-tracking.books.add') }}" class="btn-primary">Add Title</a>
                @endif
            </div>
        </div>
    </div>

    <x-form.table title="Titles" :subtitle="number_format($books->total()) . ' ' . \Illuminate\Support\Str::plural('title', $books->total()) . ($filtersActive ? ' match the filters' : '')">
        <x-slot name="tableHeaders">
            <x-data-table.th scope="col">#</x-data-table.th>
            <x-data-table.th scope="col">{{ __('Title') }}</x-data-table.th>
            <x-data-table.th scope="col">{{ __('Packaging') }}</x-data-table.th>
            <x-data-table.th scope="col">{{ __('QR Codes') }}</x-data-table.th>
            <x-data-table.th scope="col">{{ __('Allocated') }}</x-data-table.th>
            <x-data-table.th scope="col" class="th-wrap">{{ __('Student Books on Hand') }}</x-data-table.th>
            <x-data-table.th scope="col" class="th-wrap">{{ __('Teacher Guides on Hand') }}</x-data-table.th>
            <x-data-table.th scope="col" class="th-wrap">{{ __('Shipped (SB / TG)') }}</x-data-table.th>
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
                <td class="px-5 py-2 whitespace-nowrap text-sm text-gray-700 dark:text-gray-100">
                    {{ number_format((int) $book->student_sent) }}
                    <span class="text-xs text-gray-500 dark:text-gray-400">/ {{ $book->has_teacher_guide ? number_format((int) $book->teacher_sent) : '—' }}</span>
                </td>
                <td class="px-5 py-2">
                    <div class="flex items-center gap-1.5">
                        <x-ui.view-modal title="{{ $book->title }}" label="View title details">
                            <dl class="grid grid-cols-2 gap-x-4 gap-y-3">
                                <div><dt class="text-xs text-gray-500">Region</dt><dd>{{ $book->region->name ?? 'All regions' }}</dd></div>
                                <div><dt class="text-xs text-gray-500">Grade · Subject</dt><dd>{{ $book->grade_name ?: '-' }} · {{ $book->subject_name ?: '-' }}</dd></div>
                                <div><dt class="text-xs text-gray-500">Packaging</dt><dd>{{ $book->students_per_package }} student books{{ $book->has_teacher_guide ? ' + 1 teacher guide' : '' }}</dd></div>
                                <div><dt class="text-xs text-gray-500">On hand (SB / TG)</dt><dd>{{ number_format((int) $book->student_on_hand) }} / {{ $book->has_teacher_guide ? number_format((int) $book->teacher_on_hand) : '—' }}</dd></div>
                                <div><dt class="text-xs text-gray-500">Allocated (SB / TG)</dt><dd>{{ number_format((int) $book->student_allocated) }} / {{ number_format((int) $book->teacher_allocated) }}</dd></div>
                                <div><dt class="text-xs text-gray-500">Shipped so far (SB / TG)</dt><dd>{{ number_format((int) $book->student_sent) }} / {{ number_format((int) $book->teacher_sent) }}</dd></div>
                                <div><dt class="text-xs text-gray-500">Language</dt><dd>{{ $book->language ?: '—' }}</dd></div>
                                <div><dt class="text-xs text-gray-500">Code · ISBN</dt><dd>{{ $book->code ?: '—' }} · {{ $book->isbn ?: '—' }}</dd></div>
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
            <x-data-table.empty colspan="9" />
            @endforelse
        </x-slot>

        {{ $books->links() }}
    </x-form.table>
</div>
