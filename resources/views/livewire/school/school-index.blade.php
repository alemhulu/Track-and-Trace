<div class="space-y-4">
    @php
        $states = \App\Http\Livewire\School\SchoolIndex::STATES;
        $percentOf = fn ($part, $whole) => $whole > 0 ? round($part / $whole * 100, 1) : 0;
    @endphp

    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <x-ui.stat label="Schools" :value="number_format($stats['schools'])" icon="fi-rr-school"
            :percent="$percentOf($stats['enrolled_schools'], $stats['schools'])">
            {{ number_format($stats['enrolled_schools']) }} with enrolment for {{ $years[$year] ?? $year }}
        </x-ui.stat>
        <x-ui.stat label="Learners enrolled" :value="number_format($stats['enrolled'])" icon="fi-rr-id-badge" tone="info">
            {{ $percentOf($stats['female'], $stats['enrolled']) }}% female · {{ number_format($stats['students']) }} registered by name
        </x-ui.stat>
        <x-ui.stat label="Books issued to students" :value="number_format($stats['issued'])" icon="fi-rr-graduation-cap" tone="success">
            {{ $stats['students'] ? number_format($stats['issued'] / $stats['students'], 1) . ' per registered student' : 'No students registered yet' }}
        </x-ui.stat>
        <x-ui.stat label="On school shelves" :value="number_format($stats['student_on_hand'])" icon="fi-rr-book" tone="brand">
            Student books · {{ number_format($stats['teacher_on_hand']) }} teacher guides
        </x-ui.stat>
    </div>

    <div class="grid grid-cols-2 gap-3 xl:grid-cols-4">
        @foreach (['no_enrolment' => ['warning', 'fi-rr-exclamation'], 'awaiting' => ['info', 'fi-rr-truck-side'], 'never_delivered' => ['danger', 'fi-rr-inbox'], 'with_stock' => ['success', 'fi-rr-box']] as $key => [$tone, $icon])
        <x-ui.stat :label="$states[$key]" :value="number_format($stats['states'][$key])" :tone="$tone" :icon="$icon"
            :active="$state === $key" wire:click="setState('{{ $key }}')" :percent="$percentOf($stats['states'][$key], $stats['schools'])">
            {{ $percentOf($stats['states'][$key], $stats['schools']) }}% of schools · {{ $state === $key ? 'click to clear' : 'click to filter' }}
        </x-ui.stat>
        @endforeach
    </div>

    <div class="p-4 card">
        <div class="grid grid-cols-1 gap-3 md:grid-cols-3 xl:grid-cols-6">
            <x-location-picker :picker="$locationPicker" model="area" />
            <div>
                <x-jet-label for="search" value="School" />
                <x-jet-input wire:model.debounce.400ms="search" id="search" type="search" placeholder="School name" class="block w-full mt-1" />
            </div>
            <div>
                <x-jet-label for="state" value="Showing" />
                <x-form.select wire:model="state" id="state">
                    <option value="">All schools</option>
                    @foreach ($states as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </x-form.select>
            </div>
            <div>
                <x-jet-label for="year" value="Academic year" />
                <x-form.select wire:model="year" id="year">
                    @foreach ($years as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </x-form.select>
            </div>
        </div>
        @if ($filtersActive)
        <div class="flex justify-end mt-3">
            <button type="button" wire:click="clearFilters" class="text-sm link">Clear filters</button>
        </div>
        @endif
    </div>

    <x-form.table title="Schools" :search="false" :subtitle="number_format($schools->total()) . ' ' . \Illuminate\Support\Str::plural('school', $schools->total()) . ($state ? ' · ' . $states[$state] : '') . ' · coverage is books issued against books required'">
        <x-slot name="tableHeaders">
            <x-data-table.th scope="col">{{ __('School') }}</x-data-table.th>
            <x-data-table.th scope="col" class="th-wrap">{{ __('Student Books Required') }}</x-data-table.th>
            <x-data-table.th scope="col" class="th-wrap">{{ __('Issued to Students') }}</x-data-table.th>
            <x-data-table.th scope="col">{{ __('On Hand') }}</x-data-table.th>
            <x-data-table.th scope="col">{{ __('Short') }}</x-data-table.th>
            <x-data-table.th scope="col">{{ __('Coverage') }}</x-data-table.th>
            <x-data-table.th scope="col"><span class="sr-only">{{ __('View') }}</span></x-data-table.th>
        </x-slot>

        <x-slot name="tableRows">
            @forelse ($schools as $school)
            @php $total = $totals[$school->id]; @endphp
            <x-data-table.tr>
                <td class="px-5 py-2 text-sm">
                    <div class="cell-wrap">
                        <a href="{{ route('manual-tracking.schools.show', ['school' => $school->id, 'year' => $year]) }}"
                            class="font-semibold link">{{ $school->name }}</a>
                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $school->woreda->name ?? '—' }}, {{ $school->zone->name ?? '—' }}</div>
                    </div>
                </td>
                @if ($total['required_student'] === 0)
                <td colspan="5" class="px-5 py-2 text-sm text-amber-600">No enrolment or students recorded for this year.</td>
                @else
                <td class="px-5 py-2 text-sm text-gray-700 dark:text-gray-100">{{ number_format($total['required_student']) }}</td>
                <td class="px-5 py-2 text-sm text-gray-700 dark:text-gray-100">{{ number_format($total['issued_student']) }}</td>
                <td class="px-5 py-2 text-sm text-gray-700 dark:text-gray-100">{{ number_format($total['on_hand_student']) }}</td>
                <td class="px-5 py-2 text-sm {{ $total['shortfall_student'] ? 'font-semibold text-red-600' : 'text-gray-500' }}">
                    {{ number_format($total['shortfall_student']) }}
                </td>
                <td class="px-5 py-2 text-sm">
                    <x-school.coverage-bar :value="$total['coverage']" />
                </td>
                @endif
                <td class="px-5 py-2">
                    <x-action.view-link :href="route('manual-tracking.schools.show', ['school' => $school->id, 'year' => $year])" label="Open school" />
                </td>
            </x-data-table.tr>
            @empty
            <x-data-table.empty colspan="7" />
            @endforelse
        </x-slot>

        {{ $schools->links() }}
    </x-form.table>
</div>
