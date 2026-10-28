<div>
    <div class="grid grid-cols-1 gap-3 mb-3 md:grid-cols-3">
        <div>
            <x-jet-label for="search" value="School" />
            <x-jet-input wire:model.debounce.400ms="search" id="search" type="search" class="block w-full mt-1" />
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

    <x-form.table title="Schools">
        <x-slot name="tableHeaders">
            <x-data-table.th scope="col">{{ __('School') }}</x-data-table.th>
            <x-data-table.th scope="col">{{ __('Student Books Required') }}</x-data-table.th>
            <x-data-table.th scope="col">{{ __('Issued to Students') }}</x-data-table.th>
            <x-data-table.th scope="col">{{ __('On Hand') }}</x-data-table.th>
            <x-data-table.th scope="col">{{ __('Short') }}</x-data-table.th>
            <x-data-table.th scope="col">{{ __('Coverage') }}</x-data-table.th>
        </x-slot>

        <x-slot name="tableRows">
            @forelse ($schools as $school)
            @php $total = $totals[$school->id]; @endphp
            <x-data-table.tr>
                <td class="px-5 py-2 text-sm whitespace-nowrap">
                    <a href="{{ route('manual-tracking.schools.show', ['school' => $school->id, 'year' => $year]) }}"
                        class="font-semibold link">{{ $school->name }}</a>
                    <div class="text-xs text-gray-500">{{ $school->woreda->name ?? '—' }}, {{ $school->zone->name ?? '—' }}</div>
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
            </x-data-table.tr>
            @empty
            <x-data-table.empty colspan="6" />
            @endforelse
        </x-slot>

        {{ $schools->links() }}
    </x-form.table>
</div>
