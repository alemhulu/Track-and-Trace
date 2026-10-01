<div>
    <div class="mb-3 flex justify-end">
        <a href="{{ route('manual-tracking.distribution.add') }}"
            class="inline-flex items-center px-4 py-2 rounded-md bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium">
            Add Distribution Entry
        </a>
    </div>

    <x-form.table title="Manual Distribution Entries">
        <x-slot name="tableHeaders">
            <x-data-table.th scope="col">#</x-data-table.th>
            <x-data-table.th scope="col">{{ __('Reference') }}</x-data-table.th>
            <x-data-table.th scope="col">{{ __('Date') }}</x-data-table.th>
            <x-data-table.th scope="col">{{ __('Lines') }}</x-data-table.th>
            <x-data-table.th scope="col">{{ __('Quantity') }}</x-data-table.th>
            <x-data-table.th scope="col">{{ __('Remark') }}</x-data-table.th>
            <x-data-table.th scope="col" class="sr-only">{{ __('Action') }}</x-data-table.th>
        </x-slot>

        <x-slot name="tableRows">
            @forelse ($distributions as $distribution)
            <x-data-table.tr>
                <td class="px-5 py-2 whitespace-nowrap text-sm text-gray-700 dark:text-gray-100">{{ $loop->iteration }}
                </td>
                <td class="px-5 py-2 whitespace-nowrap text-sm font-semibold text-gray-700 dark:text-gray-100">
                    {{ $distribution->reference }}</td>
                <td class="px-5 py-2 whitespace-nowrap text-sm text-gray-700 dark:text-gray-100">
                    {{ optional($distribution->distributed_at)->format('Y-m-d H:i') ?: '-' }}</td>
                <td class="px-5 py-2 whitespace-nowrap text-sm text-gray-700 dark:text-gray-100">
                    {{ number_format($distribution->lines_count) }}</td>
                <td class="px-5 py-2 whitespace-nowrap text-sm text-gray-700 dark:text-gray-100">
                    {{ number_format((int) ($distribution->lines_sum_quantity ?? 0)) }}</td>
                <td class="px-5 py-2 whitespace-nowrap text-sm text-gray-700 dark:text-gray-100">
                    {{ $distribution->remarks ?: '-' }}</td>
                <td class="px-5 py-2">
                    <x-action.table-button id="{{ $distribution->id }}" delete="deleteDistribution" link />
                </td>
            </x-data-table.tr>
            @empty
            <x-data-table.empty colspan="7" />
            @endforelse
        </x-slot>

        {{ $distributions->links() }}
    </x-form.table>
</div>
