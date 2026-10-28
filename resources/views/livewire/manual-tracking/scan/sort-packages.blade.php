<div class="space-y-5">
    <div class="p-5 card">
        <h3 class="section-title">Sort packages</h3>
        <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">
            A package QR only names its zone. Choose the title you are counting (or scan one of its books), then scan each package.
            Packages that do not belong here are flagged and sent to the misroutes list.
        </p>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
            <x-location-picker :picker="$locationPicker" model="area" organization-model="organizationId"
                organization-label="Sorting at" organization-placeholder="Select warehouse or hub" mode="choose" />
            <div>
                <x-jet-label for="titleId" value="Title being counted" />
                <x-form.select wire:model="titleId" id="titleId">
                    <option value="">Not set</option>
                    @foreach ($titles as $title)
                    <option value="{{ $title->id }}">{{ $title->title }}</option>
                    @endforeach
                </x-form.select>
            </div>
            <div>
                <x-jet-label for="since" value="Count scans since" />
                <x-jet-input wire:model="since" id="since" type="date" class="block w-full mt-1" />
            </div>
        </div>

        @if ($place)
        <x-scan.scanner class="mt-4" />
        @endif
    </div>

    @if ($last)
    <x-scan.result :scan="$last" />
    @endif

    @if ($place)
    <div class="overflow-x-auto card">
        <div class="px-4 pt-4 text-sm font-semibold text-gray-800 dark:text-gray-100">Sorted at {{ $place->name }}</div>
        <table class="w-full mt-2 text-sm text-left">
            <thead>
                <tr>
                    <th class="px-4 py-2">Zone</th>
                    <th class="px-4 py-2">Title</th>
                    <th class="px-4 py-2">Packages</th>
                    <th class="px-4 py-2">Flagged</th>
                    <th class="px-4 py-2"><span class="sr-only">Ship</span></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($tally as $row)
                <tr>
                    <td class="px-4 py-2 font-medium text-gray-900 dark:text-gray-100">{{ $row->zone->name ?? 'Unresolved zone' }}</td>
                    <td class="px-4 py-2 text-gray-700 dark:text-gray-200">{{ $row->book->title ?? 'Title not set' }}</td>
                    <td class="px-4 py-2 text-gray-700 dark:text-gray-200">{{ number_format($row->packages) }}</td>
                    <td class="px-4 py-2 {{ $row->alerts ? 'text-red-600 font-semibold' : 'text-gray-400' }}">{{ (int) $row->alerts }}</td>
                    <td class="px-4 py-2 text-right">
                        @if ($row->zone && $row->book && ! $row->alerts && auth()->user()->can('manual-tracking.shipments.dispatch'))
                        <a href="{{ route('manual-tracking.distribution.add', ['from' => $place->id, 'zone' => $row->zone_id, 'book' => $row->manual_book_id, 'packages' => $row->packages]) }}"
                            class="text-sm link">Ship to zone</a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-4 py-4 text-center text-gray-500">No packages scanned in this period.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <x-scan.log :scans="$recent" />
    @endif
</div>
