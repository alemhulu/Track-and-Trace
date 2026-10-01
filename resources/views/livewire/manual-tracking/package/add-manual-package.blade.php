<div>
    <x-form.card function="savePackage" title="{{ $editingPackageId ? 'Edit Manual Package' : 'Add Manual Package' }}"
        submitLabel="{{ $editingPackageId ? 'Update' : 'Add' }}">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <x-jet-label for="manual_book_id" value="Book" />
                <x-form.select wire:model.live="manual_book_id" id="manual_book_id" class="mt-1 block w-full">
                    <option value="">Select Book</option>
                    @foreach ($books as $book)
                    <option value="{{ $book->id }}">{{ $book->title }}</option>
                    @endforeach
                </x-form.select>
                <x-jet-input-error for="manual_book_id" class="mt-2" />
            </div>

            @if ($manual_book_id)
            <div class="md:col-span-2 rounded-lg border border-blue-200 bg-blue-50 p-3 text-sm text-blue-900">
                <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                    <span class="font-semibold">Selected book: {{ $selectedBookTitle ?: 'Unknown Book' }}</span>
                    <span>Total copies: <strong>{{ number_format($selectedBookTotalCopies) }}</strong></span>
                </div>
                <div class="mt-2 text-blue-700">
                    Calculated package total: <strong>{{ number_format($packageTotalBooks) }}</strong>
                    @if ($packageTotalBooks > $selectedBookTotalCopies)
                    <span class="ml-2 text-red-600 font-semibold"> exceeds the selected book total</span>
                    @endif
                </div>
            </div>
            @endif

            <div>
                <x-jet-label for="package_code" value="Package Code" />
                <x-jet-input wire:model.defer="package_code" id="package_code" type="text" class="mt-1 block w-full" />
                <x-jet-input-error for="package_code" class="mt-2" />
            </div>

            <div>
                <x-jet-label for="no_of_packages" value="Number of Packages" />
                <x-jet-input wire:model.live="no_of_packages" id="no_of_packages" type="number" min="1"
                    class="mt-1 block w-full" />
                <x-jet-input-error for="no_of_packages" class="mt-2" />
            </div>

            <div>
                <x-jet-label for="books_per_package" value="Books Per Package" />
                <x-jet-input wire:model.live="books_per_package" id="books_per_package" type="number" min="0"
                    class="mt-1 block w-full" />
                <x-jet-input-error for="books_per_package" class="mt-2" />
            </div>

            <div>
                <x-jet-label for="status" value="Status" />
                <x-form.select wire:model.defer="status" id="status" class="mt-1 block w-full">
                    <option value="available">Available</option>
                    <option value="partial">Partially Distributed</option>
                    <option value="closed">Closed</option>
                </x-form.select>
                <x-jet-input-error for="status" class="mt-2" />
            </div>

            <div class="md:col-span-2">
                <x-jet-label for="notes" value="Notes" />
                <textarea wire:model.defer="notes" id="notes" rows="3"
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100"></textarea>
                <x-jet-input-error for="notes" class="mt-2" />
            </div>
        </div>
    </x-form.card>
</div>
