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
                        <span class="badge-brand">{{ $record->steps_count }} steps</span>
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
                    <span class="badge-success"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Active</span>
                    @else
                    <span class="badge-danger"><span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>Inactive</span>
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
