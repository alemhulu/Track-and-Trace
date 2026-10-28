<div>
    <div class="flex flex-col gap-3 mb-3 md:flex-row md:items-end md:justify-between">
        <div class="grid grid-cols-1 gap-3 md:grid-cols-2 md:w-2/3">
            <div>
                <x-jet-label for="organizationFilter" value="Location" />
                <x-form.select wire:model="organizationFilter" id="organizationFilter">
                    <option value="">All locations in my area</option>
                    @foreach ($organizations as $organization)
                    <option value="{{ $organization->id }}">{{ $organization->name }}</option>
                    @endforeach
                </x-form.select>
            </div>
            <div>
                <x-jet-label for="bookFilter" value="Title" />
                <x-form.select wire:model="bookFilter" id="bookFilter">
                    <option value="">All titles</option>
                    @foreach ($books as $book)
                    <option value="{{ $book->id }}">{{ $book->title }}</option>
                    @endforeach
                </x-form.select>
            </div>
        </div>

        @if ($canRecordOpening)
        <button type="button" wire:click="$toggle('showOpeningForm')"
            class="btn-primary">
            {{ $showOpeningForm ? 'Close' : 'Record Opening Stock' }}
        </button>
        @endif
    </div>

    @if ($canRecordOpening && $showOpeningForm)
    <div class="p-5 mb-5 bg-white border border-blue-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-blue-800">
        <h3 class="card-title">Opening stock</h3>
        <p class="mb-4 text-xs text-gray-500 dark:text-gray-400">
            Books already in a warehouse before tracking started. Count whole packages plus any partial package.
        </p>

        <form wire:submit.prevent="recordOpeningStock" class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div>
                <x-jet-label for="opening-organization" value="Location" />
                <x-form.select wire:model="opening.organization_id" id="opening-organization">
                    <option value="">Select location</option>
                    @foreach ($organizations as $organization)
                    <option value="{{ $organization->id }}">{{ $organization->name }}</option>
                    @endforeach
                </x-form.select>
            </div>
            <div class="md:col-span-2">
                <x-jet-label for="opening-book" value="Title" />
                <x-form.select wire:model="opening.manual_book_id" id="opening-book">
                    <option value="">Select title</option>
                    @foreach ($books as $book)
                    <option value="{{ $book->id }}">{{ $book->title }}</option>
                    @endforeach
                </x-form.select>
            </div>
            <div>
                <x-jet-label for="opening-full" value="Full packages" />
                <x-jet-input wire:model="opening.full_packages" id="opening-full" type="number" min="0" class="block w-full mt-1" />
            </div>
            <div>
                <x-jet-label for="opening-partial" value="Student books in partial package" />
                <x-jet-input wire:model="opening.partial_package_books" id="opening-partial" type="number" min="0" class="block w-full mt-1" />
            </div>
            <div>
                <x-jet-label for="opening-partial-tg" value="Teacher guides in partial package" />
                <x-jet-input wire:model="opening.partial_teacher_guides" id="opening-partial-tg" type="number" min="0" class="block w-full mt-1" />
            </div>
            <div class="md:col-span-2">
                <x-jet-label for="opening-notes" value="Notes" />
                <x-jet-input wire:model.defer="opening.notes" id="opening-notes" type="text" class="block w-full mt-1" />
            </div>
            <div class="flex items-end justify-between gap-3">
                <p class="text-sm text-gray-600 dark:text-gray-300">
                    = <strong>{{ number_format($openingCopies['student']) }}</strong> student books,
                    <strong>{{ number_format($openingCopies['teacher']) }}</strong> teacher guides
                </p>
                <button type="submit"
                    class="btn-success">Record</button>
            </div>
        </form>
    </div>
    @endif

    <x-form.table title="Stock by Location">
        <x-slot name="tableHeaders">
            <x-data-table.th scope="col">{{ __('Location') }}</x-data-table.th>
            <x-data-table.th scope="col">{{ __('Title') }}</x-data-table.th>
            <x-data-table.th scope="col">{{ __('Student Books on Hand') }}</x-data-table.th>
            <x-data-table.th scope="col">{{ __('Teacher Guides on Hand') }}</x-data-table.th>
            <x-data-table.th scope="col">{{ __('Dispatched, Not Yet Received') }}</x-data-table.th>
        </x-slot>

        <x-slot name="tableRows">
            @forelse ($balances as $balance)
            <x-data-table.tr>
                <td class="px-5 py-2 text-sm text-gray-700 whitespace-nowrap dark:text-gray-100">{{ $balance->organization->name ?? '—' }}</td>
                <td class="px-5 py-2 text-sm text-gray-700 whitespace-nowrap dark:text-gray-100">{{ $balance->book->title ?? '—' }}</td>
                <td class="px-5 py-2 text-sm font-semibold text-gray-700 whitespace-nowrap dark:text-gray-100">
                    {{ number_format((int) $balance->student_on_hand) }}
                    <span class="text-xs font-normal text-gray-400">
                        ≈ {{ number_format(intdiv((int) $balance->student_on_hand, max(1, $balance->book->students_per_package ?? 40))) }} pkg
                    </span>
                </td>
                <td class="px-5 py-2 text-sm text-gray-700 whitespace-nowrap dark:text-gray-100">{{ number_format((int) $balance->teacher_on_hand) }}</td>
                <td class="px-5 py-2 text-sm text-gray-700 whitespace-nowrap dark:text-gray-100">
                    {{ number_format((int) $balance->student_in_transit) }} / {{ number_format((int) $balance->teacher_in_transit) }}
                </td>
            </x-data-table.tr>
            @empty
            <x-data-table.empty colspan="5" />
            @endforelse
        </x-slot>

        {{ $balances->links() }}
    </x-form.table>
</div>
