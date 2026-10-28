<div class="space-y-4">
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        <form wire:submit.prevent="saveStudent" class="p-5 bg-white rounded-lg shadow-sm dark:bg-gray-800">
            <h4 class="mb-3 text-sm font-semibold text-gray-800 dark:text-gray-100">{{ $editingId ? 'Edit student' : 'Add student' }}</h4>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <x-jet-label for="student_no" value="Student number" />
                    <x-jet-input wire:model.defer="form.student_no" id="student_no" type="text" class="block w-full mt-1" />
                    <x-jet-input-error for="form.student_no" class="mt-1" />
                </div>
                <div>
                    <x-jet-label for="student_name" value="Full name" />
                    <x-jet-input wire:model.defer="form.name" id="student_name" type="text" class="block w-full mt-1" />
                    <x-jet-input-error for="form.name" class="mt-1" />
                </div>
                <div>
                    <x-jet-label for="student_sex" value="Sex" />
                    <x-form.select wire:model.defer="form.sex" id="student_sex">
                        <option value="">Select</option>
                        <option value="M">Male</option>
                        <option value="F">Female</option>
                    </x-form.select>
                </div>
                <div>
                    <x-jet-label for="student_grade" value="Grade" />
                    <x-form.select wire:model.defer="form.grade_id" id="student_grade">
                        <option value="">Select</option>
                        @foreach ($grades as $grade)
                        <option value="{{ $grade->id }}">Grade {{ $grade->name }}</option>
                        @endforeach
                    </x-form.select>
                </div>
                <div>
                    <x-jet-label for="student_section" value="Section" />
                    <x-jet-input wire:model.defer="form.section" id="student_section" type="text" placeholder="A" class="block w-full mt-1" />
                </div>
                <div>
                    <x-jet-label for="student_status" value="Status" />
                    <x-form.select wire:model.defer="form.status" id="student_status">
                        @foreach (\App\Models\School\Student::STATUSES as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </x-form.select>
                </div>
            </div>
            <div class="flex justify-end gap-2 mt-4">
                @if ($editingId)
                <button type="button" wire:click="cancelEdit" class="px-3 py-2 text-sm text-gray-700 bg-gray-100 rounded-md dark:bg-gray-700 dark:text-gray-200">Cancel</button>
                @endif
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700">Save</button>
            </div>
        </form>

        <form wire:submit.prevent="import" class="p-5 bg-white rounded-lg shadow-sm dark:bg-gray-800">
            <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-100">Import from CSV</h4>
            <p class="mt-1 mb-3 text-xs text-gray-500 dark:text-gray-400">
                Columns: <code>student_no, name, sex, grade, section</code>. Sex is M or F; grade can be "6" or "Grade 6".
                Existing student numbers are updated. If any row has a problem, nothing is imported.
            </p>
            <input type="file" wire:model="csv" accept=".csv,text/csv" class="block w-full text-sm text-gray-700 dark:text-gray-200">
            <x-jet-input-error for="csv" class="mt-1" />
            <div class="flex justify-end mt-4">
                <button type="submit" wire:loading.attr="disabled" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 disabled:opacity-50">Import</button>
            </div>
            @if ($importErrors)
            <ul class="mt-3 space-y-1 overflow-y-auto text-xs text-red-700 max-h-40">
                @foreach ($importErrors as $line => $error)
                <li>Line {{ $line }}: {{ $error }}</li>
                @endforeach
            </ul>
            @endif
        </form>
    </div>

    <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
        <div>
            <x-jet-label for="register-search" value="Search" />
            <x-jet-input wire:model.debounce.400ms="search" id="register-search" type="search" placeholder="Name or number" class="block w-full mt-1" />
        </div>
        <div>
            <x-jet-label for="register-grade" value="Grade" />
            <x-form.select wire:model="gradeFilter" id="register-grade">
                <option value="">All grades</option>
                @foreach ($grades as $grade)
                <option value="{{ $grade->id }}">Grade {{ $grade->name }}</option>
                @endforeach
            </x-form.select>
        </div>
    </div>

    <div class="overflow-x-auto bg-white rounded-lg shadow-sm dark:bg-gray-800">
        <table class="w-full text-sm text-left">
            <thead class="text-xs text-gray-600 bg-gray-50 dark:bg-gray-700 dark:text-gray-300">
                <tr>
                    <th class="px-4 py-2">Number</th>
                    <th class="px-4 py-2">Name</th>
                    <th class="px-4 py-2">Sex</th>
                    <th class="px-4 py-2">Class</th>
                    <th class="px-4 py-2">Status</th>
                    <th class="px-4 py-2">Books held</th>
                    <th class="px-4 py-2"><span class="sr-only">Edit</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse ($students as $student)
                <tr wire:key="student-{{ $student->id }}">
                    <td class="px-4 py-2 text-gray-700 dark:text-gray-300">{{ $student->student_no }}</td>
                    <td class="px-4 py-2 font-medium text-gray-900 dark:text-gray-100">{{ $student->name }}</td>
                    <td class="px-4 py-2 text-gray-700 dark:text-gray-300">{{ $student->sex }}</td>
                    <td class="px-4 py-2 text-gray-700 dark:text-gray-300">Grade {{ $student->grade->name }}{{ $student->section ? ' ' . $student->section : '' }}</td>
                    <td class="px-4 py-2 {{ $student->status === 'active' ? 'text-gray-700 dark:text-gray-300' : 'text-amber-600' }}">{{ \App\Models\School\Student::STATUSES[$student->status] ?? $student->status }}</td>
                    <td class="px-4 py-2 text-gray-700 dark:text-gray-300">{{ $student->held_books_count }}</td>
                    <td class="px-4 py-2 text-right">
                        <button type="button" wire:click="editStudent({{ $student->id }})" class="text-blue-700 hover:underline dark:text-blue-300">Edit</button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-4 py-6 text-center text-gray-500">No students registered for this year.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-3">{{ $students->links() }}</div>
    </div>
</div>
