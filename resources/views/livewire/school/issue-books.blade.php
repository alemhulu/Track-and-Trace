<div class="space-y-4">
    @if ($grades->isEmpty())
    <div class="p-5 text-sm card text-amber-700 dark:text-amber-300">
        Register this year's students first; books are issued to individual students.
    </div>
    @else
    <div class="flex flex-wrap items-end gap-3 p-5 card">
        <div class="w-40">
            <x-jet-label for="issue-grade" value="Grade" />
            <x-form.select wire:model="gradeId" id="issue-grade">
                @foreach ($grades as $grade)
                <option value="{{ $grade->id }}">Grade {{ $grade->name }}</option>
                @endforeach
            </x-form.select>
        </div>
        <div class="w-40">
            <x-jet-label for="issue-section" value="Section" />
            <x-form.select wire:model="section" id="issue-section">
                <option value="">All sections</option>
                @foreach ($sections as $option)
                <option value="{{ $option }}">{{ $option }}</option>
                @endforeach
            </x-form.select>
        </div>
        <div class="w-40">
            <x-jet-label for="issue-condition" value="Condition" />
            <x-form.select wire:model="condition" id="issue-condition">
                @foreach (\App\Models\School\StudentBookIssue::CONDITIONS as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </x-form.select>
        </div>
        <div class="flex flex-wrap items-center gap-2 ml-auto">
            <a href="{{ route('manual-tracking.schools.print', ['school' => $schoolId, 'grade' => $gradeId, 'section' => $section, 'year' => $year]) }}" target="_blank"
                class="btn-secondary">Print class sheet</a>
            <button type="button" wire:click="selectMissing" class="px-3 py-2 text-sm font-medium text-blue-700 bg-blue-50 rounded-md hover:bg-blue-100 dark:bg-gray-700 dark:text-blue-300">Select everyone missing a book</button>
            <button type="button" wire:click="clearSelection" class="btn-secondary">Clear</button>
            <button type="button" wire:click="issue" wire:loading.attr="disabled" class="btn-success">
                Issue selected
            </button>
        </div>
    </div>

    <div class="p-4 card">
        @if ($scanStudent)
        <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
            <p class="text-sm text-gray-800 dark:text-gray-100">
                Scanning books for <strong>{{ $scanStudent->name }}</strong> <span class="text-gray-500">{{ $scanStudent->student_no }}</span>.
                Scan the QR on each book as you hand it over.
            </p>
            <button type="button" wire:click="nextStudent" class="px-3 py-1 text-sm font-medium text-blue-700 bg-blue-50 rounded-md hover:bg-blue-100 dark:bg-gray-700 dark:text-blue-300">Next student →</button>
        </div>
        @else
        <p class="mb-2 text-sm text-gray-500 dark:text-gray-400">To hand out books one student at a time, press <strong>Scan</strong> beside a student, then scan each book's QR.</p>
        @endif
        <x-scan.scanner placeholder="Scan the book's QR or type the code printed under it" />
    </div>

    @if ($returning)
    <div class="flex flex-wrap items-end gap-3 p-4 border rounded-lg bg-amber-50 border-amber-200 dark:bg-gray-800 dark:border-amber-700">
        <p class="text-sm text-gray-800 dark:text-gray-100">
            Return <strong>{{ $returning->book->title }}</strong> from <strong>{{ $returning->student->name }}</strong>
        </p>
        <div class="w-56">
            <x-form.select wire:model="returnCondition">
                @foreach (\App\Models\School\StudentBookIssue::CONDITIONS + \App\Models\School\StudentBookIssue::UNUSABLE_RETURN_CONDITIONS as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </x-form.select>
        </div>
        <button type="button" wire:click="confirmReturn" class="btn-warning">Record return</button>
        <button type="button" wire:click="cancelReturn" class="px-3 py-2 text-sm text-gray-700">Cancel</button>
    </div>
    @endif

    <div class="overflow-x-auto card">
        <table class="table-ui">
            <thead>
                <tr>
                    <th class="px-4 py-2">Student</th>
                    @foreach ($titles as $title)
                    <th class="px-4 py-2 text-center">
                        {{ $title->title }}
                        <span class="block font-normal text-gray-500">{{ number_format($onHand[$title->id] ?? 0) }} on hand</span>
                    </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse ($students as $student)
                <tr wire:key="issue-row-{{ $student->id }}" class="{{ $scanStudentId === $student->id ? 'bg-blue-50 dark:bg-blue-900/20' : '' }}">
                    <td class="px-4 py-2">
                        <button type="button" wire:click="scanFor({{ $student->id }})"
                            class="mr-2 text-xs {{ $scanStudentId === $student->id ? 'font-semibold text-blue-700 dark:text-blue-300' : 'text-gray-500 hover:underline' }}">Scan</button>
                        <span class="font-medium text-gray-900 dark:text-gray-100">{{ $student->name }}</span>
                        <span class="text-xs text-gray-500">{{ $student->student_no }}{{ $student->section ? ' · ' . $student->section : '' }}</span>
                    </td>
                    @foreach ($titles as $title)
                    @php $key = $student->id . '_' . $title->id; @endphp
                    <td class="px-4 py-2 text-center">
                        @if (isset($held[$key]))
                        <span class="text-xs font-semibold text-green-700">✓ Holds</span>
                        <button type="button" wire:click="startReturn({{ $held[$key] }})" class="block mx-auto text-xs text-gray-500 hover:underline">Return</button>
                        @else
                        <input type="checkbox" wire:model.defer="selected.{{ $key }}" aria-label="Issue {{ $title->title }} to {{ $student->name }}"
                            class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                        @endif
                    </td>
                    @endforeach
                </tr>
                @empty
                <tr><td colspan="{{ $titles->count() + 1 }}" class="px-4 py-6 text-center text-gray-500">No active students in this class.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if ($titles->isEmpty())
        <p class="p-4 text-sm text-amber-700">No titles are registered for this grade and region yet.</p>
        @endif
    </div>
    @endif
</div>
