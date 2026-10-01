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
            <x-data-table.th scope="col">{{ __('Subject') }}</x-data-table.th>
            <x-data-table.th scope="col">{{ __('Source') }}</x-data-table.th>
            <x-data-table.th scope="col">{{ __('Destination') }}</x-data-table.th>
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
                    {{ $distribution->reference }}
                </td>
                <td class="px-5 py-2 whitespace-nowrap text-sm text-gray-700 dark:text-gray-100">
                    {{ optional($distribution->distributed_at)->format('Y-m-d H:i') ?: '-' }}
                </td>
                <td class="px-5 py-2 whitespace-nowrap text-sm text-gray-700 dark:text-gray-100">
                    @php
                    $subjects = $distribution->lines
                    ->pluck('book.subject_name')
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();
                    @endphp
                    {{ empty($subjects) ? '-' : implode(', ', $subjects) }}
                </td>
                <td class="px-5 py-2 whitespace-nowrap text-sm text-gray-700 dark:text-gray-100">
                    @php
                    $sourceLabel = $distribution->organization?->name
                    ?? $distribution->woreda?->name
                    ?? $distribution->zone?->name
                    ?? $distribution->region?->name
                    ?? $distribution->country?->name
                    ?? '-';
                    @endphp
                    <a href="#viewDistribution{{ $distribution->id }}"
                        class="font-medium text-blue-700 hover:text-blue-800 hover:underline dark:text-blue-300 dark:hover:text-blue-200">
                        {{ $sourceLabel }}
                    </a>
                </td>
                <td class="px-5 py-2 whitespace-nowrap text-sm text-gray-700 dark:text-gray-100">
                    @php
                    $destinationLabel = $distribution->destinationOrganization?->name
                    ?? $distribution->destinationWoreda?->name
                    ?? $distribution->destinationZone?->name
                    ?? $distribution->destinationRegion?->name
                    ?? $distribution->destinationCountry?->name
                    ?? '-';
                    @endphp
                    <a href="#viewDistribution{{ $distribution->id }}"
                        class="font-medium text-blue-700 hover:text-blue-800 hover:underline dark:text-blue-300 dark:hover:text-blue-200">
                        {{ $destinationLabel }}
                    </a>
                </td>
                <td class="px-5 py-2 whitespace-nowrap text-sm text-gray-700 dark:text-gray-100">
                    {{ number_format($distribution->lines_count) }}
                </td>
                <td class="px-5 py-2 whitespace-nowrap text-sm text-gray-700 dark:text-gray-100">
                    {{ number_format((int) ($distribution->lines_sum_quantity ?? 0)) }}
                </td>
                <td class="px-5 py-2 whitespace-nowrap text-sm text-gray-700 dark:text-gray-100">
                    {{ $distribution->remarks ?: '-' }}
                </td>
                <td class="px-5 py-2">
                    <x-action.table-button id="{{ $distribution->id }}" view="#viewDistribution{{ $distribution->id }}"
                        delete="deleteDistribution" link />
                </td>
            </x-data-table.tr>

            <x-data-table.modal name="viewDistribution{{ $distribution->id }}" maxWidth="3xl" :buttons="false">
                <x-slot name="title">Distribution Detail</x-slot>
                <x-slot name="body">
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                            <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                                <div class="text-xs text-gray-500">Reference</div>
                                <div class="mt-1 font-semibold text-gray-800">{{ $distribution->reference }}</div>
                            </div>
                            <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                                <div class="text-xs text-gray-500">Distributed At</div>
                                <div class="mt-1 font-semibold text-gray-800">
                                    {{ optional($distribution->distributed_at)->format('Y-m-d H:i') ?: '-' }}
                                </div>
                            </div>
                            <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                                <div class="text-xs text-gray-500">Book</div>
                                <div class="mt-1 font-semibold text-gray-800">
                                    @php $firstLineBook = $distribution->lines->first()?->book; @endphp
                                    {{ $firstLineBook?->title ?? '-' }}
                                </div>
                            </div>
                            <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                                <div class="text-xs text-gray-500">Subject</div>
                                <div class="mt-1 font-semibold text-gray-800">
                                    @php $subjectNames =
                                    $distribution->lines->pluck('book.subject_name')->filter()->unique()->values()->all();
                                    @endphp
                                    {{ empty($subjectNames) ? '-' : implode(', ', $subjectNames) }}
                                </div>
                            </div>
                            <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                                <div class="text-xs text-gray-500">Total Quantity</div>
                                <div class="mt-1 font-semibold text-gray-800">
                                    {{ number_format((int) ($distribution->lines_sum_quantity ?? 0)) }}
                                </div>
                            </div>
                            <div class="rounded-lg border border-gray-200 bg-gray-50 p-3 sm:col-span-2">
                                <div class="text-xs text-gray-500">Source</div>
                                @php
                                $sourceChain = collect([
                                $distribution->country?->name,
                                $distribution->region?->name,
                                $distribution->zone?->name,
                                $distribution->woreda?->name,
                                ])->filter()->values()->all();
                                @endphp
                                <div class="mt-1 font-semibold text-gray-800">
                                    {{ $distribution->organization?->name ?? $distribution->country?->name ?? 'N/A' }}
                                    @if (! empty($sourceChain))
                                    <div class="mt-1 text-xs text-gray-600">
                                        {{ implode(' > ', $sourceChain) }}
                                    </div>
                                    @endif
                                </div>
                            </div>
                            <div class="rounded-lg border border-gray-200 bg-gray-50 p-3 sm:col-span-2">
                                <div class="text-xs text-gray-500">Destination</div>
                                @php
                                $destinationChain = collect([
                                $distribution->destinationCountry?->name,
                                $distribution->destinationRegion?->name,
                                $distribution->destinationZone?->name,
                                $distribution->destinationWoreda?->name,
                                ])->filter()->values()->all();
                                @endphp
                                <div class="mt-1 font-semibold text-gray-800">
                                    {{ $distribution->destinationOrganization?->name ?? $distribution->destinationCountry?->name ?? 'N/A' }}
                                    @if (! empty($destinationChain))
                                    <div class="mt-1 text-xs text-gray-600">
                                        {{ implode(' > ', $destinationChain) }}
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        @if ($distribution->lines->isNotEmpty())
                        <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                            <div class="text-xs text-gray-500 mb-2">Distribution Lines</div>
                            <div class="space-y-2">
                                @foreach ($distribution->lines as $line)
                                <div
                                    class="flex flex-col gap-1 rounded border border-gray-200 bg-white p-2 sm:flex-row sm:items-center sm:justify-between">
                                    <div>
                                        <div class="font-semibold text-gray-800">{{ $line->book->title ?? '-' }}</div>
                                        <div class="text-xs text-gray-500">
                                            {{ $line->package ? 'Package: ' . $line->package->package_code : 'Book only' }}
                                        </div>
                                    </div>
                                    <div class="text-sm font-semibold text-gray-700">Qty:
                                        {{ number_format($line->quantity) }}
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        @if ($distribution->remarks)
                        <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                            <div class="text-xs text-gray-500">Remarks</div>
                            <div class="mt-1 text-sm text-gray-800">{{ $distribution->remarks }}</div>
                        </div>
                        @endif
                    </div>
                </x-slot>
            </x-data-table.modal>
            @empty
            <x-data-table.empty colspan="10" />
            @endforelse
        </x-slot>

        {{ $distributions->links() }}
    </x-form.table>
</div>
