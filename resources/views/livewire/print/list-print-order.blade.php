<div>
    <div>
        <div class="mb-4 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-300 mb-1">From Date</label>
                <input type="date" wire:model="createdFrom"
                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-300 mb-1">To Date</label>
                <input type="date" wire:model="createdTo"
                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-300 mb-1">Status</label>
                <select wire:model="statusFilter"
                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm">
                    <option value="">All Statuses</option>
                    <option value="0">Requested</option>
                    <option value="1">Accepted</option>
                    <option value="2">Printed</option>
                    <option value="3">Sent</option>
                    <option value="4">Rejected</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-300 mb-1">Order By Created At</label>
                <select wire:model="sortType"
                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm">
                    <option value="desc">Newest First</option>
                    <option value="asc">Oldest First</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-300 mb-1">Rows Per Page</label>
                <select wire:model="recordes"
                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm">
                    <option value="5">5</option>
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
            </div>
        </div>

        <x-form.table title="Organizations List">
            <x-slot name="tableHeaders">
                <x-data-table.th scope="col">#</x-data-table.th>
                <x-data-table.th scope="col"> {{__('Book') }}</x-data-table.th>
                <x-data-table.th scope="col"> {{__('Print Info') }}</x-data-table.th>
                <x-data-table.th scope="col"> {{__('Organization') }}</x-data-table.th>
                <x-data-table.th scope="col"> {{__('Printer') }}</x-data-table.th>
                <x-data-table.th scope="col"> {{__('Order Status') }}</x-data-table.th>
                <x-data-table.th scope="col" class="sr-only">{{__('Action') }}</x-data-table.th>
            </x-slot>

            <x-slot name="tableRows">
                @forelse($orders as $key=>$record)
                <x-data-table.tr>
                    <td class="px-5 py-2 whitespace-nowrap">
                        <div class="text-sm text-gray-700 dark:text-gray-100">{{ $orders->firstItem() + $key }}</div>
                    </td>

                    <td class="px-5 py-2 whitespace-nowrap">
                        <x-book.book-info image="{{ $record->book->front_cover_location }}"
                            grade="Grade {{ $record->book->grade->name }}" subject="{{ $record->book->subject->name }}"
                            type="{{ $record->book->book_type ?  'Teacher Guide' : 'Student Text Book'}}"
                            edition="{{ $record->book->edition }}st Edition {{ $record->book->created_at->format('Y') }}"
                            ISBN="{{ $record->book->isbn }}" />
                    </td>

                    <td class="px-5 py-2 whitespace-nowrap">
                        <x-book.print-info batch="PB{{ $record->created_at->format('Y') }}{{ $record->id }}"
                            copies="{{ $record->no_of_books }} + {{ $record->no_of_books/$record->no_of_packages }}"
                            packages="{{ $record->no_of_books/$record->no_of_packages }}" />
                    </td>

                    <td class="px-5 py-2 whitespace-nowrap">
                        <x-organization.info image="{{ $record->orderOrganization->logo }}"
                            name="{{ $record->orderOrganization->name }}"
                            email="{{ $record->orderOrganization->email }}"
                            phone="+{{ $record->orderOrganization->telephone }}" />
                    </td>

                    <td class="px-5 py-2 whitespace-nowrap">
                        <x-organization.info image="{{ $record->printOrganization->logo }}"
                            name="{{ $record->printOrganization->name }}"
                            email="{{ $record->printOrganization->email }}"
                            phone="+{{ $record->printOrganization->telephone }}" />
                    </td>

                    <td class="px-5 py-2 whitespace-nowrap">
                        <x-button
                            btnType="{{ $record->request_status == 0 ? 'warning' : ($record->request_status == 1 ? 'primary' : ( $record->request_status == 2 ?  'success' : ($record->request_status == 3 ?  'info' : 'danger')) )}}">
                            {{ $record->request_status == 0 ? 'Requested' : ($record->request_status == 1 ? 'Accepted' : ( $record->request_status == 2 ?  'Printed' : ($record->request_status == 3 ?  'Sent' : 'Rejected')) )}}
                        </x-button>
                    </td>

                    <td class="px-5 py-2">
                        <x-action.table-button id="{{ $record->id }}"
                            view="{{ route('print-order.view', $record->id) }}" link />
                    </td>
                </x-data-table.tr>
                @empty
                <x-data-table.empty colspan=6 />
                @endforelse
            </x-slot>
            {{$orders->links()}}
        </x-form.table>
    </div>
</div>