<div class="space-y-4">
    @if (session('success'))
    <div class="alert-success">{{ session('success') }}</div>
    @endif

    <div class="flex flex-wrap items-end gap-3 p-5 card">
        <div class="flex-1">
            <h3 class="section-title">Verification visits</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                A supervisor counts books on the school's shelves and asks a random sample of students to show the books recorded as theirs.
            </p>
        </div>
        <div class="w-48">
            <x-jet-label for="visit-year" value="Academic year" />
            <x-form.select wire:model="year" id="visit-year">
                @foreach ($years as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </x-form.select>
        </div>
        @can('manual-tracking.visits.record')
        <form wire:submit.prevent="startVisit" class="flex items-end gap-2">
            <div class="w-64">
                <x-jet-label for="visit-school" value="Visit a school" />
                <x-form.select wire:model.defer="schoolId" id="visit-school">
                    <option value="">Choose school</option>
                    @foreach ($schools as $school)
                    <option value="{{ $school->id }}">{{ $school->name }}</option>
                    @endforeach
                </x-form.select>
                <x-jet-input-error for="schoolId" class="mt-1" />
            </div>
            <button type="submit" class="btn-success">Start visit</button>
        </form>
        @endcan
        <a href="{{ route('manual-tracking.exports', ['type' => 'visits', 'year' => $year]) }}"
            class="btn-secondary">Export CSV</a>
    </div>

    @php $results = \App\Http\Livewire\ManualTracking\Oversight\VisitIndex::RESULTS; @endphp
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <x-ui.stat label="Visits" :value="number_format($stats['visits'])" icon="fi-rr-marker">
            By {{ number_format($stats['visitors']) }} {{ \Illuminate\Support\Str::plural('supervisor', $stats['visitors']) }} · {{ number_format($stats['students']) }} students checked
        </x-ui.stat>
        <x-ui.stat label="Schools visited" :value="number_format($stats['schools'])" icon="fi-rr-school" tone="info"
            :percent="$stats['school_total'] ? round($stats['schools'] / $stats['school_total'] * 100, 1) : 0">
            of {{ number_format($stats['school_total']) }} schools in the area
        </x-ui.stat>
        <x-ui.stat label="Shelf counts matching" :value="$stats['stock_accuracy'] !== null ? $stats['stock_accuracy'] . '%' : '—'" icon="fi-rr-book" tone="success"
            :percent="$stats['stock_accuracy']">
            Over {{ number_format($stats['stock_lines']) }} titles counted
        </x-ui.stat>
        <x-ui.stat label="Books shown by students" :value="$stats['holding_rate'] !== null ? $stats['holding_rate'] . '%' : '—'" icon="fi-rr-graduation-cap" tone="success"
            :percent="$stats['holding_rate']">
            {{ number_format($stats['books_seen']) }} of {{ number_format($stats['books_expected']) }} books recorded with them
        </x-ui.stat>
    </div>

    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
        @foreach (['clean' => ['success', 'fi-rr-check'], 'shelf' => ['warning', 'fi-rr-box'], 'students' => ['danger', 'fi-rr-exclamation']] as $key => [$tone, $icon])
        <x-ui.stat :label="$results[$key]" :value="number_format($stats['results'][$key])" :tone="$tone" :icon="$icon"
            :active="$result === $key" wire:click="setResult('{{ $key }}')">
            {{ $stats['visits'] ? round($stats['results'][$key] / $stats['visits'] * 100, 1) : 0 }}% of visits · {{ $result === $key ? 'click to clear' : 'click to filter' }}
        </x-ui.stat>
        @endforeach
    </div>

    <div class="p-4 card">
        <div class="grid grid-cols-1 gap-3 md:grid-cols-4 xl:grid-cols-7">
            <x-location-picker :picker="$locationPicker" model="area" />
            <div>
                <x-jet-label for="visit-search" value="Search" />
                <x-jet-input wire:model.debounce.400ms="search" id="visit-search" type="search" placeholder="School or supervisor" class="block w-full mt-1" />
            </div>
            <div>
                <x-jet-label for="visit-result" value="Result" />
                <x-form.select wire:model="result" id="visit-result">
                    <option value="">Any result</option>
                    @foreach ($results as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </x-form.select>
            </div>
            <div>
                <x-jet-label for="visit-from" value="Visited from" />
                <x-jet-input wire:model="dateFrom" id="visit-from" type="date" class="block w-full mt-1" />
            </div>
            <div>
                <x-jet-label for="visit-to" value="Visited to" />
                <x-jet-input wire:model="dateTo" id="visit-to" type="date" class="block w-full mt-1" />
            </div>
        </div>
        <div class="flex flex-wrap items-center justify-between gap-2 mt-3">
            <p class="text-xs text-gray-500 dark:text-gray-400">{{ number_format($visits->total()) }} {{ \Illuminate\Support\Str::plural('visit', $visits->total()) }} shown</p>
            @if ($filtersActive)
            <button type="button" wire:click="clearFilters" class="text-sm link">Clear filters</button>
            @endif
        </div>
    </div>

    <div class="overflow-x-auto card">
        <table class="table-ui">
            <thead>
                <tr>
                    <th class="px-4 py-2">Date</th>
                    <th class="px-4 py-2">School</th>
                    <th class="px-4 py-2">Visited by</th>
                    <th class="px-4 py-2">Shelf counts matching</th>
                    <th class="px-4 py-2">Books shown by students</th>
                    <th class="px-4 py-2"><span class="sr-only">View</span></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($visits as $visit)
                <tr>
                    <td class="px-4 py-2 whitespace-nowrap">
                        {{ $visit->visited_at->format('Y-m-d') }}
                    </td>
                    <td class="px-4 py-2 text-gray-700 dark:text-gray-200">
                        <div class="cell-wrap">
                        <a href="{{ route('manual-tracking.visits.show', $visit->id) }}" class="font-medium link">{{ $visit->school->name ?? '—' }}</a>
                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $visit->school->woreda->name ?? '' }}{{ isset($visit->school->zone) ? ', ' . $visit->school->zone->name : '' }}</div>
                        </div>
                    </td>
                    <td class="px-4 py-2 text-gray-700 dark:text-gray-200">{{ $visit->visitor->name ?? '—' }}</td>
                    <td class="px-4 py-2">
                        @if ($visit->stock_lines)
                        <x-school.coverage-bar :value="$visit->stockAccuracy()" hint="Titles whose shelf count matched the system" />
                        <span class="text-xs text-gray-500">{{ $visit->stock_lines_matching }} of {{ $visit->stock_lines }} titles</span>
                        @else
                        <span class="text-gray-400">Not counted</span>
                        @endif
                    </td>
                    <td class="px-4 py-2">
                        @if ($visit->books_expected)
                        <x-school.coverage-bar :value="$visit->holdingRate()" hint="Books students could show" />
                        <span class="text-xs text-gray-500">{{ $visit->books_seen }} of {{ $visit->books_expected }} books, {{ $visit->students_checked }} students</span>
                        @else
                        <span class="text-gray-400">No students checked</span>
                        @endif
                    </td>
                    <td class="px-4 py-2">
                        <x-action.view-link :href="route('manual-tracking.visits.show', $visit->id)" label="View visit" />
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-4 py-6 text-center text-gray-500">{{ $filtersActive ? 'No visits match the filters.' : 'No visits recorded this year.' }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $visits->links() }}
</div>
