<div x-data="{ tab: 'coverage' }">
    <div class="flex flex-wrap items-end justify-between gap-3 p-5 mb-4 card">
        <div>
            <a href="{{ route('manual-tracking.schools.index', ['year' => $year]) }}" class="text-xs link">← All schools</a>
            <h3 class="section-title text-xl">{{ $school->name }}</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                {{ $school->woreda->name ?? '—' }}, {{ $school->zone->name ?? '—' }}, {{ $school->region->name ?? '—' }}
            </p>
        </div>
        <div class="w-56">
            <x-jet-label for="year" value="Academic year" />
            <x-form.select wire:model="year" id="year">
                @foreach ($years as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </x-form.select>
        </div>
    </div>

    <div role="tablist" aria-label="School records" class="flex flex-wrap gap-1 p-1 mb-4 bg-gray-100 rounded-lg dark:bg-gray-800 w-fit">
        @foreach (['coverage' => 'Coverage', 'enrolment' => 'Enrolment', 'students' => 'Students', 'issue' => 'Issue Books', 'guides' => 'Teacher Guides'] as $key => $label)
        <button type="button" role="tab" :aria-selected="(tab === '{{ $key }}').toString()" @click="tab = '{{ $key }}'"
            :class="tab === '{{ $key }}' ? 'bg-white text-gray-900 shadow dark:bg-gray-700 dark:text-white' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400'"
            class="px-4 py-2 text-sm font-semibold rounded-md">{{ $label }}</button>
        @endforeach
    </div>

    {{-- Keyed by year so every tab reloads when the year changes --}}
    <div x-show="tab === 'coverage'">@livewire('school.school-coverage', ['schoolId' => $schoolId, 'year' => $year], key('coverage-' . $year))</div>
    <div x-show="tab === 'enrolment'" style="display: none">@livewire('school.enrolment-editor', ['schoolId' => $schoolId, 'year' => $year], key('enrolment-' . $year))</div>
    <div x-show="tab === 'students'" style="display: none">@livewire('school.student-register', ['schoolId' => $schoolId, 'year' => $year], key('students-' . $year))</div>
    <div x-show="tab === 'issue'" style="display: none">@livewire('school.issue-books', ['schoolId' => $schoolId, 'year' => $year], key('issue-' . $year))</div>
    <div x-show="tab === 'guides'" style="display: none">@livewire('school.teacher-guides', ['schoolId' => $schoolId, 'year' => $year], key('guides-' . $year))</div>
</div>
