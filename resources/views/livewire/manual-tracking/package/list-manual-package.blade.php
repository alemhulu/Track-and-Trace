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
            <x-data-table.th scope="col">{{ __('Subject') }}</x-data-table.th>
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
                    {{ $package->package_code }}
                </td>
                <td class="px-5 py-2 whitespace-nowrap text-sm text-gray-700 dark:text-gray-100">
                    {{ $package->book->title ?? '-' }}
                </td>
                <td class="px-5 py-2 whitespace-nowrap text-sm text-gray-700 dark:text-gray-100">
                    {{ $package->book->subject_name ?? '-' }}
                </td>
                <td class="px-5 py-2 whitespace-nowrap text-sm text-gray-700 dark:text-gray-100">
                    {{ number_format($package->total_books) }}
                </td>
                <td class="px-5 py-2 whitespace-nowrap text-sm font-semibold text-gray-700 dark:text-gray-100">
                    {{ number_format($package->current_balance) }}
                </td>
                <td class="px-5 py-2 whitespace-nowrap text-sm text-gray-700 dark:text-gray-100">{{ $package->status }}
                </td>
                <td class="px-5 py-2">
                    <x-action.table-button id="{{ $package->id }}" view="#viewPackage{{ $package->id }}"
                        edit="editPackage" delete="deletePackage" link />
                </td>
            </x-data-table.tr>

            <x-data-table.modal name="viewPackage{{ $package->id }}" maxWidth="2xl" :buttons="false">
                <x-slot name="title">Package Detail</x-slot>
                <x-slot name="body">
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                            <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                                <div class="text-xs text-gray-500">Package Code</div>
                                <div class="mt-1 font-semibold text-gray-800">{{ $package->package_code }}</div>
                            </div>
                            <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                                <div class="text-xs text-gray-500">Book</div>
                                <div class="mt-1 font-semibold text-gray-800">{{ $package->book->title ?? '-' }}</div>
                            </div>
                            <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                                <div class="text-xs text-gray-500">Subject</div>
                                <div class="mt-1 font-semibold text-gray-800">{{ $package->book->subject_name ?? '-' }}
                                </div>
                            </div>
                            <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                                <div class="text-xs text-gray-500">Grade</div>
                                <div class="mt-1 font-semibold text-gray-800">{{ $package->book->grade_name ?? '-' }}
                                </div>
                            </div>
                            <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                                <div class="text-xs text-gray-500">Book Total Copies</div>
                                <div class="mt-1 font-semibold text-gray-800">
                                    {{ number_format((int) ($package->book->total_copies ?? 0)) }}</div>
                            </div>
                            <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                                <div class="text-xs text-gray-500">Number of Packages</div>
                                <div class="mt-1 font-semibold text-gray-800">
                                    {{ number_format($package->no_of_packages) }}
                                </div>
                            </div>
                            <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                                <div class="text-xs text-gray-500">Books Per Package</div>
                                <div class="mt-1 font-semibold text-gray-800">
                                    {{ number_format($package->books_per_package) }}
                                </div>
                            </div>
                            <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                                <div class="text-xs text-gray-500">Total Books</div>
                                <div class="mt-1 font-semibold text-gray-800">{{ number_format($package->total_books) }}
                                </div>
                            </div>
                            <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                                <div class="text-xs text-gray-500">Current Balance</div>
                                <div class="mt-1 font-semibold text-gray-800">
                                    {{ number_format($package->current_balance) }}
                                </div>
                            </div>
                            <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                                <div class="text-xs text-gray-500">Status</div>
                                <div class="mt-1 font-semibold text-gray-800">{{ $package->status }}</div>
                            </div>
                            <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                                <div class="text-xs text-gray-500">Created</div>
                                <div class="mt-1 font-semibold text-gray-800">
                                    {{ optional($package->created_at)->format('Y-m-d H:i') ?: '-' }}
                                </div>
                            </div>
                        </div>

                        @if ($package->notes)
                        <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                            <div class="text-xs text-gray-500">Notes</div>
                            <div class="mt-1 text-sm text-gray-800">{{ $package->notes }}</div>
                        </div>
                        @endif
                    </div>
                </x-slot>
            </x-data-table.modal>
            @empty
            <x-data-table.empty colspan="8" />
            @endforelse
        </x-slot>

        {{ $packages->links() }}
    </x-form.table>
</div>
