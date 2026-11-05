<div class="space-y-4">
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <x-ui.stat label="Student books on hand" :value="number_format($stats['student'])" icon="fi-rr-graduation-cap" tone="success">
            ≈ {{ number_format($stats['packages']) }} full packages
        </x-ui.stat>
        <x-ui.stat label="Teacher guides on hand" :value="number_format($stats['teacher'])" icon="fi-rr-book-alt" tone="success">
            {{ $stats['student'] > 0 ? '1 TG per ' . number_format(round($stats['student'] / max(1, $stats['teacher']))) . ' SB' : 'No student books held' }}
        </x-ui.stat>
        <x-ui.stat label="Locations holding stock" :value="number_format($stats['locations'])" icon="fi-rr-building" tone="brand"
            :active="$holding === 'on_hand'" wire:click="setHolding('on_hand')">
            {{ number_format($stats['titles']) }} {{ \Illuminate\Support\Str::plural('title', $stats['titles']) }} held · click to show rows with stock
        </x-ui.stat>
        <x-ui.stat label="Dispatched, not received" :value="number_format($stats['student_moving'] + $stats['teacher_moving'])" icon="fi-rr-truck-side" tone="warning"
            :active="$holding === 'in_transit'" wire:click="setHolding('in_transit')">
            {{ number_format($stats['student_moving']) }} SB · {{ number_format($stats['teacher_moving']) }} TG from {{ number_format($stats['sending']) }} {{ \Illuminate\Support\Str::plural('location', $stats['sending']) }}
        </x-ui.stat>
    </div>

    @if ($stats['incoming_shipments'])
    <div class="flex flex-wrap items-center justify-between gap-2 px-4 py-3 text-sm rounded-lg text-sky-800 bg-sky-50 dark:bg-sky-900/30 dark:text-sky-200">
        <span>
            <i class="inline-flex mr-1 align-middle fi fi-rr-inbox"></i>
            <strong>{{ number_format($stats['incoming_shipments']) }}</strong> {{ \Illuminate\Support\Str::plural('shipment', $stats['incoming_shipments']) }} on the way in:
            {{ number_format($stats['incoming_student']) }} SB and {{ number_format($stats['incoming_teacher']) }} TG will be added once received.
        </span>
        <a href="{{ route('manual-tracking.distribution.list', ['statusFilter' => 'dispatched']) }}" class="link">View shipments</a>
    </div>
    @endif

    <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
        <div class="grid grid-cols-1 gap-3 md:grid-cols-3 xl:grid-cols-6 md:flex-1">
            <x-location-picker :picker="$locationPicker" model="area" organization-model="organizationFilter"
                organization-label="Location" organization-placeholder="All locations in my area" />
            <div>
                <x-jet-label for="bookFilter" value="Title" />
                <x-form.select wire:model="bookFilter" id="bookFilter">
                    <option value="">All titles</option>
                    @foreach ($books as $book)
                    <option value="{{ $book->id }}">{{ $book->title }}</option>
                    @endforeach
                </x-form.select>
            </div>
            <div>
                <x-jet-label for="holding" value="Showing" />
                <x-form.select wire:model="holding" id="holding">
                    <option value="">All stock rows</option>
                    <option value="on_hand">With copies on hand</option>
                    <option value="in_transit">With copies on the road</option>
                </x-form.select>
            </div>
        </div>

        @if ($canRecordOpening)
        <button type="button" wire:click="$toggle('showOpeningForm')"
            class="btn-primary">
            {{ $showOpeningForm ? 'Close' : 'Record Opening Stock' }}
        </button>
        @endif
    </div>

    @if ($canRecordOpening && $showOpeningForm)
    <div class="p-5 bg-white border border-blue-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-blue-800">
        <h3 class="card-title">Opening stock</h3>
        <p class="mb-4 text-xs text-gray-500 dark:text-gray-400">
            Books already in a warehouse before tracking started. Count whole packages plus any partial package.
        </p>

        <form wire:submit.prevent="recordOpeningStock" class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-4 md:col-span-3">
                <x-location-picker :picker="$openingPicker" model="openingArea" organization-model="opening.organization_id"
                    organization-label="Location" organization-placeholder="Select location" mode="form" />
            </div>
            <div class="md:col-span-3">
                <x-jet-label for="opening-book" value="Title" />
                <x-form.select wire:model="opening.manual_book_id" id="opening-book">
                    <option value="">{{ $openingBooks->isEmpty() ? 'No titles for this region' : 'Select title' }}</option>
                    @foreach ($openingBooks as $book)
                    <option value="{{ $book->id }}">{{ $book->title }}</option>
                    @endforeach
                </x-form.select>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Only titles used in the location's region are listed.</p>
            </div>

            @if ($opening['manual_book_id'] && $openingStanding)
            <div class="space-y-2 md:col-span-3" wire:key="opening-standing">
                @foreach ($openingStanding as $row)
                @php $over = $row['over_student'] + $row['over_teacher'] > 0; @endphp
                <div wire:key="standing-{{ $row['level'] }}" class="p-3 text-sm rounded-lg {{ $over ? 'bg-red-50 text-red-800 dark:bg-red-900/30 dark:text-red-200' : 'bg-sky-50 text-sky-900 dark:bg-sky-900/30 dark:text-sky-100' }}">
                    <p class="font-semibold">{{ $row['level'] }} allocation · {{ $row['name'] }}</p>
                    <div class="grid grid-cols-2 gap-2 mt-1 md:grid-cols-4">
                        <div><span class="block text-xs opacity-75">Allocated (SB / TG)</span>{{ number_format($row['student']) }} / {{ number_format($row['teacher']) }}</div>
                        <div><span class="block text-xs opacity-75">Already held there (SB / TG)</span>{{ number_format($row['held_student']) }} / {{ number_format($row['held_teacher']) }}</div>
                        <div><span class="block text-xs opacity-75">Still allowed (SB / TG)</span>{{ number_format(max(0, $row['room_student'])) }} / {{ number_format(max(0, $row['room_teacher'])) }}</div>
                        <div><span class="block text-xs opacity-75">This entry (SB / TG)</span>{{ number_format($openingCopies['student']) }} / {{ number_format($openingCopies['teacher']) }}</div>
                    </div>
                    @if ($over)
                    <p class="mt-1 font-semibold">
                        This would put {{ $row['level'] === 'Zone' ? 'the zone' : 'the region' }}
                        {{ number_format($row['over_student']) }} SB{{ $row['over_teacher'] ? ' and ' . number_format($row['over_teacher']) . ' TG' : '' }} over its allocation, so it cannot be recorded. Lower the count to what is still allowed.
                    </p>
                    @endif
                    <p class="mt-1 text-xs opacity-75">Held = on hand or on the road at locations there, plus shipments still on their way in.</p>
                </div>
                @endforeach

                @if ($openingRoom)
                @php $packing = $openingRoom['packing']; @endphp
                <div wire:key="opening-prediction" class="flex flex-wrap items-center justify-between gap-2 p-3 text-sm border rounded-lg border-sky-200 dark:border-sky-800">
                    <span>
                        <span>You can still stock:</span>
                        <strong>{{ number_format($packing['full_packages']) }} full {{ \Illuminate\Support\Str::plural('package', $packing['full_packages']) }}</strong>
                        @if ($packing['partial_package_books'] || $packing['partial_teacher_guides'])<span>+ partial ({{ $packing['partial_package_books'] }} SB, {{ $packing['partial_teacher_guides'] }} TG)</span>@endif
                        @if ($packing['extra_teacher_guides'])<span>+ {{ number_format($packing['extra_teacher_guides']) }} extra TG</span>@endif
                        <span class="block text-xs opacity-75">= {{ number_format($openingRoom['student']) }} SB and {{ number_format($openingRoom['teacher']) }} TG, the smallest room left in the region and zone.</span>
                    </span>
                    @if ($openingRoom['student'] + $openingRoom['teacher'] > 0)
                    <button type="button" wire:click="fillAllowed" class="btn-secondary btn-sm">Fill allowed amount</button>
                    @endif
                </div>
                @endif
            </div>
            @elseif ($opening['manual_book_id'] && $opening['organization_id'])
            <p wire:key="opening-no-allocation" class="text-xs text-amber-700 md:col-span-3 dark:text-amber-300">This title has no allocation for the location's region in the latest allocation year.</p>
            @endif

            <div>
                <x-jet-label for="opening-full" value="Full packages" />
                <x-jet-input wire:model="opening.full_packages" id="opening-full" type="number" min="0" class="block w-full mt-1" />
            </div>
            <div>
                <x-jet-label for="opening-partial" value="Student books in partial package" />
                <x-jet-input wire:model="opening.partial_package_books" id="opening-partial" type="number" min="0" class="block w-full mt-1" />
            </div>
            <div>
                <x-jet-label for="opening-partial-tg" value="Teacher guides in partial package" />
                <x-jet-input wire:model="opening.partial_teacher_guides" id="opening-partial-tg" type="number" min="0" class="block w-full mt-1" />
            </div>
            <div>
                <x-jet-label for="opening-extra-tg" value="Extra teacher guides (packed separately)" />
                <x-jet-input wire:model="opening.extra_teacher_guides" id="opening-extra-tg" type="number" min="0" class="block w-full mt-1"
                    title="Teacher guides beyond one per package, for when the allocation has more teacher guides than packages" />
            </div>
            <div class="md:col-span-2">
                <x-jet-label for="opening-notes" value="Notes" />
                <x-jet-input wire:model.defer="opening.notes" id="opening-notes" type="text" class="block w-full mt-1" />
            </div>
            <div class="flex items-end justify-between gap-3">
                <p class="text-sm text-gray-600 dark:text-gray-300">
                    <span>=</span> <strong>{{ number_format($openingCopies['student']) }}</strong> <span>student books,</span>
                    <strong>{{ number_format($openingCopies['teacher']) }}</strong> <span>teacher guides</span>
                </p>
                @php $overAllocation = collect($openingStanding)->contains(fn ($row) => $row['over_student'] + $row['over_teacher'] > 0); @endphp
                <button type="submit" class="btn-success" @disabled($overAllocation)
                    title="{{ $overAllocation ? 'Over the allocation: lower the count first' : '' }}">Record</button>
            </div>
        </form>
    </div>
    @endif

    <x-form.table title="Stock by Location" :subtitle="number_format($balances->total()) . ' location-title ' . \Illuminate\Support\Str::plural('row', $balances->total())">
        <x-slot name="tableHeaders">
            <x-data-table.th scope="col">{{ __('Location') }}</x-data-table.th>
            <x-data-table.th scope="col">{{ __('Title') }}</x-data-table.th>
            <x-data-table.th scope="col" class="th-wrap">{{ __('Student Books on Hand') }}</x-data-table.th>
            <x-data-table.th scope="col" class="th-wrap">{{ __('Teacher Guides on Hand') }}</x-data-table.th>
            <x-data-table.th scope="col" class="th-wrap">{{ __('Dispatched, Not Yet Received (SB / TG)') }}</x-data-table.th>
        </x-slot>

        <x-slot name="tableRows">
            @forelse ($balances as $balance)
            <x-data-table.tr>
                <td class="px-5 py-2 text-sm text-gray-700 dark:text-gray-100"><div class="cell-wrap">{{ $balance->organization->name ?? '—' }}</div></td>
                <td class="px-5 py-2 text-sm text-gray-700 dark:text-gray-100"><div class="cell-wrap">{{ $balance->book->title ?? '—' }}</div></td>
                <td class="px-5 py-2 text-sm font-semibold text-gray-700 whitespace-nowrap dark:text-gray-100">
                    {{ number_format((int) $balance->student_on_hand) }}
                    <span class="text-xs font-normal text-gray-500 dark:text-gray-400">
                        ≈ {{ number_format(intdiv((int) $balance->student_on_hand, max(1, $balance->book->students_per_package ?? 40))) }} pkg
                    </span>
                </td>
                <td class="px-5 py-2 text-sm text-gray-700 whitespace-nowrap dark:text-gray-100">{{ number_format((int) $balance->teacher_on_hand) }}</td>
                <td class="px-5 py-2 text-sm text-gray-700 whitespace-nowrap dark:text-gray-100">
                    {{ number_format((int) $balance->student_in_transit) }} / {{ number_format((int) $balance->teacher_in_transit) }}
                </td>
            </x-data-table.tr>
            @empty
            <x-data-table.empty colspan="5" />
            @endforelse
        </x-slot>

        {{ $balances->links() }}
    </x-form.table>
</div>
