<div class="space-y-4">
    @if ($rows->isEmpty())
    <div class="p-5 text-sm card text-amber-700 dark:text-amber-300">
        Record this year's enrolment or student register to see what the school needs.
    </div>
    @else
    <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
        @foreach ([
            'Student books required' => number_format($totals['required_student']),
            'With students' => number_format($totals['issued_student']),
            'On hand at school' => number_format($totals['on_hand_student']),
            'Still short' => number_format($totals['shortfall_student']),
        ] as $label => $value)
        <div class="p-4 card">
            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $label }}</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $value }}</p>
        </div>
        @endforeach
    </div>

    <div class="overflow-x-auto card">
        <table class="table-ui">
            <thead>
                <tr>
                    <th class="px-4 py-2">Title</th>
                    <th class="px-4 py-2">Enrolled / Registered</th>
                    <th class="px-4 py-2">Student books: required · on hand · issued · short</th>
                    <th class="px-4 py-2">Teacher guides: required · on hand · issued · short</th>
                    <th class="px-4 py-2">Coverage</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $row)
                <tr>
                    <td class="px-4 py-2 font-medium text-gray-900 dark:text-gray-100">{{ $row['book']->title }}</td>
                    <td class="px-4 py-2 text-gray-700 dark:text-gray-300">
                        {{ number_format($row['enrolled']) }} / {{ number_format($row['registered']) }}
                        @if ($row['enrolled'] && $row['registered'] && $row['enrolled'] !== $row['registered'])
                        <span class="block text-xs text-amber-600">Register differs from enrolment</span>
                        @endif
                    </td>
                    <td class="px-4 py-2 text-gray-700 dark:text-gray-300">
                        {{ number_format($row['required_student']) }} · {{ number_format($row['on_hand_student']) }} ·
                        {{ number_format($row['issued_student']) }} ·
                        <span class="{{ $row['shortfall_student'] ? 'font-semibold text-red-600' : '' }}">{{ number_format($row['shortfall_student']) }}</span>
                    </td>
                    <td class="px-4 py-2 text-gray-700 dark:text-gray-300">
                        @if ($row['book']->has_teacher_guide)
                        {{ $row['required_teacher'] }} · {{ $row['on_hand_teacher'] }} · {{ $row['issued_teacher'] }} ·
                        <span class="{{ $row['shortfall_teacher'] ? 'font-semibold text-red-600' : '' }}">{{ $row['shortfall_teacher'] }}</span>
                        @else
                        <span class="text-gray-400">No teacher guide</span>
                        @endif
                    </td>
                    <td class="px-4 py-2"><x-school.coverage-bar :value="$row['coverage']" /></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <p class="text-xs text-gray-500 dark:text-gray-400">
        Required is the enrolment (or the register when no enrolment is recorded). Teacher guides: one per section, with a section of up to 40 students unless the school records its own count.
    </p>
    @endif
</div>
