<div>
    <x-form.table title="Book Distribution Information List">
        <x-slot name="tableHeaders">
            <x-data-table.th scope="col">#</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Name') }}</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Steps ') }}</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Packages') }}</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Books') }}</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Status') }}</x-data-table.th>
            <x-data-table.th scope="col" class="sr-only">{{__('Action') }}</x-data-table.th>
        </x-slot>

        <x-slot name="tableRows">
            @forelse($distributions as $record)
            <x-data-table.tr>
                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-700 dark:text-gray-100">
                        {{ $distributions->firstItem() + $loop->index }}
                    </div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-600 font-semibold dark:text-gray-300">{{ $record->name }}</div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-700 dark:text-gray-100">
                        <div class=" rounded-full bg-blue-500 w-10 h-10 flex items-center justify-center">
                            <span class="text-sm text-blue-50 font-bold">{{ $record->steps_count }}</span>
                        </div>
                    </div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-500 dark:text-gray-300">
                        {{ number_format((int) ($record->tracked_packages_total ?? 0)) }} Packages
                    </div>
                </td>

                <td class="px-5 py-2 flex-wrap">
                    <div class="text-sm text-gray-500 dark:text-gray-300">
                        {{ number_format((int) ($record->tracked_books_total ?? 0)) }} Books
                    </div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    @if($record->is_active)
                    <x-button btnType="success" class="py-1 relative pl-6 pr-2 font-bold">
                        <i class="fi fi-rr-checkbox flex inset-0 top-1 left-1 absolute text-base"></i>Active
                    </x-button>
                    @else
                    <x-button btnType="danger" class="py-1 relative pl-6 pr-2 font-bold">
                        <i class="fi fi-rr-cross-circle flex inset-0 top-1 left-1 absolute text-base"></i>InActive
                    </x-button>
                    @endif
                </td>

                <td class="px-5 py-2">
                    <x-action.table-button id="{{ $record->id }}" view="showDistribution" />
                </td>
            </x-data-table.tr>
            @empty
            <x-data-table.empty colspan=7 />
            @endforelse
        </x-slot>

        {{ $distributions->links() }}
    </x-form.table>
</div>
