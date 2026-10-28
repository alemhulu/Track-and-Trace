<div class="space-y-4">
    @if (session('success'))
    <div class="p-3 text-sm text-green-800 bg-green-100 rounded-md">{{ session('success') }}</div>
    @endif

    <div class="flex flex-wrap items-end gap-3 p-5 bg-white rounded-lg shadow-sm dark:bg-gray-800">
        <div class="flex-1">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Verification visits</h3>
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
            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700">Start visit</button>
        </form>
        <a href="{{ route('manual-tracking.exports', ['type' => 'visits', 'year' => $year]) }}"
            class="px-3 py-2 text-sm text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200">Export CSV</a>
    </div>

    <div class="overflow-x-auto bg-white rounded-lg shadow-sm dark:bg-gray-800">
        <table class="w-full text-sm text-left">
            <thead class="text-xs text-gray-600 bg-gray-50 dark:bg-gray-700 dark:text-gray-300">
                <tr>
                    <th class="px-4 py-2">Date</th>
                    <th class="px-4 py-2">School</th>
                    <th class="px-4 py-2">Visited by</th>
                    <th class="px-4 py-2">Shelf counts matching</th>
                    <th class="px-4 py-2">Books shown by students</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse ($visits as $visit)
                <tr>
                    <td class="px-4 py-2 whitespace-nowrap">
                        <a href="{{ route('manual-tracking.visits.show', $visit->id) }}" class="font-medium text-blue-700 hover:underline dark:text-blue-300">{{ $visit->visited_at->format('Y-m-d') }}</a>
                    </td>
                    <td class="px-4 py-2 text-gray-700 dark:text-gray-200">
                        {{ $visit->school->name ?? '—' }}
                        <div class="text-xs text-gray-500">{{ $visit->school->woreda->name ?? '' }}{{ isset($visit->school->zone) ? ', ' . $visit->school->zone->name : '' }}</div>
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
                </tr>
                @empty
                <tr><td colspan="5" class="px-4 py-6 text-center text-gray-500">No visits recorded this year.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $visits->links() }}
</div>
