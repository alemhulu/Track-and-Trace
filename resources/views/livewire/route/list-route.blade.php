<div>
    <x-form.table title="Route List">
        <x-slot name="tableHeaders">
            <x-data-table.th scope="col">#</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Name') }}</x-data-table.th>
            <x-data-table.th scope="col"> {{__('From') }}</x-data-table.th>
            <x-data-table.th scope="col"> {{__('To ') }}</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Status') }}</x-data-table.th>
            <x-data-table.th scope="col" class="sr-only">{{__('Action') }}</x-data-table.th>
        </x-slot>

        <x-slot name="tableRows">
            @php $i = 1; @endphp
            @forelse($routes as $record)
            <x-data-table.tr>
                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-700 dark:text-gray-100">{{ $i++ }}</div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-700 dark:text-gray-100">{{ $record->name }}</div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-700 dark:text-gray-100">
                        Branch {{ optional($record->fromWarehouse)->branch ?? 'N/A' }}
                        - {{ optional(optional($record->fromWarehouse)->organization)->name ?? 'N/A' }}
                    </div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-700 dark:text-gray-100">
                        Branch {{ optional($record->toWarehouse)->branch ?? 'N/A' }}
                        - {{ optional(optional($record->toWarehouse)->organization)->name ?? 'N/A' }}
                    </div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    @if($record->is_active)
                    <x-button btnType="success" class="py-1">Active</x-button>
                    @else
                    <x-button btnType="danger" class="py-1">InActive</x-button>
                    @endif
                </td>

                <td class="px-5 py-2">
                    <x-action.table-button id="{{ $record->id }}" view="viewRoute" edit="editRoute" delete="deleteId" />
                </td>
            </x-data-table.tr>
            @empty
            <x-data-table.empty colspan=6 />
            @endforelse
        </x-slot>

        {{ $routes->links() }}
    </x-form.table>
</div>
