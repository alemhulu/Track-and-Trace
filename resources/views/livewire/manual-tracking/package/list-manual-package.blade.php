<div>
    <div class="mb-3 flex justify-end">
        <a href="{{ route('manual-tracking.packages.add') }}"
            class="inline-flex items-center px-4 py-2 rounded-md bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium">
            Add Manual Package
        </a>
    </div>

    <x-form.table title="Manual Packages">
        <x-slot name="tableHeaders">
            <x-data-table.th scope="col">#</x-data-table.th>
            <x-data-table.th scope="col">{{ __('Package') }}</x-data-table.th>
            <x-data-table.th scope="col">{{ __('Book') }}</x-data-table.th>
            <x-data-table.th scope="col">{{ __('Books') }}</x-data-table.th>
            <x-data-table.th scope="col">{{ __('Balance') }}</x-data-table.th>
            <x-data-table.th scope="col">{{ __('Status') }}</x-data-table.th>
            <x-data-table.th scope="col" class="sr-only">{{ __('Action') }}</x-data-table.th>
        </x-slot>

        <x-slot name="tableRows">
            @forelse ($packages as $package)
            <x-data-table.tr>
                <td class="px-5 py-2 whitespace-nowrap text-sm text-gray-700 dark:text-gray-100">{{ $loop->iteration }}
                </td>
                <td class="px-5 py-2 whitespace-nowrap text-sm text-gray-700 dark:text-gray-100">
                    {{ $package->package_code }}</td>
                <td class="px-5 py-2 whitespace-nowrap text-sm text-gray-700 dark:text-gray-100">
                    {{ $package->book->title ?? '-' }}</td>
                <td class="px-5 py-2 whitespace-nowrap text-sm text-gray-700 dark:text-gray-100">
                    {{ number_format($package->total_books) }}</td>
                <td class="px-5 py-2 whitespace-nowrap text-sm font-semibold text-gray-700 dark:text-gray-100">
                    {{ number_format($package->current_balance) }}</td>
                <td class="px-5 py-2 whitespace-nowrap text-sm text-gray-700 dark:text-gray-100">{{ $package->status }}
                </td>
                <td class="px-5 py-2">
                    <x-action.table-button id="{{ $package->id }}" edit="editPackage" delete="deletePackage" link />
                </td>
            </x-data-table.tr>
            @empty
            <x-data-table.empty colspan="7" />
            @endforelse
        </x-slot>

        {{ $packages->links() }}
    </x-form.table>
</div>
