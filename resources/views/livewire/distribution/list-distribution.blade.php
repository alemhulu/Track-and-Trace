<div>
    <x-form.table title="Distribution List">
        <x-slot name="tableHeaders">
            <x-data-table.th scope="col">#</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Name') }}</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Description') }}</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Steps ') }}</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Status') }}</x-data-table.th>
            <x-data-table.th scope="col" class="sr-only">{{__('Action') }}</x-data-table.th>
        </x-slot>

        <x-slot name="tableRows">
            @php $i = 1; @endphp
            @forelse($distributions as $record)
            <x-data-table.tr>
                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-700 dark:text-gray-100">{{ $i++ }}</div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-600 dark:text-gray-300 font-semibold">{{ $record->name }}</div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-500 dark:text-gray-300">{{ $record->description ?? 'N/A' }}</div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-700 dark:text-gray-100">
                        <div class=" rounded-full bg-blue-500 w-10 h-10 flex items-center justify-center">
                            <span class="text-sm text-blue-50 font-bold">{{ $record->steps_count }}</span>
                        </div>
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
                    <x-action.table-button id="{{ $record->id }}" view="viewDistribution" edit="editDistribution"
                        delete="deleteId" />
                </td>
            </x-data-table.tr>
            @empty
            <x-data-table.empty colspan=6 />
            @endforelse
        </x-slot>

        {{ $distributions->links() }}
    </x-form.table>
</div>
