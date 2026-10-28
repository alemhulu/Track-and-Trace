<div class="space-y-4">
    @if (session('success'))
    <div class="alert-success">{{ session('success') }}</div>
    @endif

    <div class="p-5 card">
        <p class="text-xs text-gray-500 uppercase">Verification visit · {{ $visit->visited_at->format('Y-m-d H:i') }}</p>
        <h3 class="section-title">{{ $visit->school->name ?? '—' }}</h3>
        <p class="text-sm text-gray-500">By {{ $visit->visitor->name ?? '—' }}</p>
        <div class="grid grid-cols-1 gap-4 mt-4 md:grid-cols-2">
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-300">Shelf counts matching the system</p>
                @if ($visit->stock_lines)
                <x-school.coverage-bar :value="$visit->stockAccuracy()" hint="Titles whose shelf count matched the system" />
                <p class="text-xs text-gray-500">{{ $visit->stock_lines_matching }} of {{ $visit->stock_lines }} titles</p>
                @else
                <p class="text-sm text-gray-400">Not counted</p>
                @endif
            </div>
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-300">Books students could show</p>
                @if ($visit->books_expected)
                <x-school.coverage-bar :value="$visit->holdingRate()" hint="Books students could show" />
                <p class="text-xs text-gray-500">{{ $visit->books_seen }} of {{ $visit->books_expected }} books, {{ $visit->students_checked }} students</p>
                @else
                <p class="text-sm text-gray-400">No students checked</p>
                @endif
            </div>
        </div>
        @if ($visit->notes)
        <p class="mt-3 text-sm text-gray-700 dark:text-gray-200"><span class="text-gray-500">Notes:</span> {{ $visit->notes }}</p>
        @endif
    </div>

    @if ($stockItems->isNotEmpty())
    <div class="overflow-x-auto card">
        <table class="table-ui">
            <thead>
                <tr><th class="px-4 py-2">Title</th><th class="px-4 py-2">In the system</th><th class="px-4 py-2">Counted</th><th class="px-4 py-2">Difference</th></tr>
            </thead>
            <tbody>
                @foreach ($stockItems as $item)
                @php $difference = $item->found - $item->expected; @endphp
                <tr>
                    <td class="px-4 py-2 text-gray-900 dark:text-gray-100">{{ $item->book->title ?? '—' }}</td>
                    <td class="px-4 py-2">{{ number_format($item->expected) }}</td>
                    <td class="px-4 py-2">{{ number_format($item->found) }}</td>
                    <td class="px-4 py-2 font-semibold {{ $difference ? 'text-red-600' : 'text-green-700' }}">{{ $difference > 0 ? '+' : '' }}{{ $difference }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @if ($studentItems->isNotEmpty())
    <div class="p-5 card">
        <h4 class="mb-2 font-semibold text-gray-800 dark:text-gray-100">Students checked</h4>
        @foreach ($studentItems as $items)
        @php $student = $items->first()->student; @endphp
        <div class="py-2 text-sm border-b border-gray-100 dark:border-gray-700">
            <span class="font-medium text-gray-900 dark:text-gray-100">{{ $student->name ?? 'Removed student' }}</span>
            <span class="text-xs text-gray-500">{{ $student->student_no ?? '' }}</span>
            <div class="flex flex-wrap gap-x-4 mt-1">
                @foreach ($items as $item)
                <span class="{{ $item->found ? 'text-green-700' : 'text-red-600 font-semibold' }}">{{ $item->found ? '✓' : '✗' }} {{ $item->book->title ?? '' }}</span>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>
