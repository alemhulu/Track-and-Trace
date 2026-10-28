<form wire:submit.prevent="save" class="space-y-4">
    <div class="p-5 card">
        <p class="text-xs text-gray-500 uppercase">Verification visit</p>
        <h3 class="section-title">{{ $school->name }}</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400">
            Nothing here changes stock. Differences are recorded so they can be followed up.
        </p>
    </div>

    <div class="p-5 card">
        <h4 class="font-semibold text-gray-800 dark:text-gray-100">1. Count the student books on the shelf</h4>
        <p class="mb-3 text-xs text-gray-500">Count before looking at any record; the system figure is shown after saving. Leave a title blank if you did not count it.</p>
        <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
            @forelse ($titles as $title)
            <label class="flex items-center justify-between gap-3 text-sm text-gray-700 dark:text-gray-200">
                <span>{{ $title->title }}</span>
                <x-jet-input wire:model.defer="shelf.{{ $title->id }}" type="number" min="0" inputmode="numeric" class="w-28" />
            </label>
            @empty
            <p class="text-sm text-gray-500">No titles are held by or printed for this school's region.</p>
            @endforelse
        </div>
        <x-jet-input-error for="shelf.*" class="mt-2" />
    </div>

    <div class="p-5 card">
        <h4 class="font-semibold text-gray-800 dark:text-gray-100">2. Ask these students to show their books</h4>
        <p class="mb-3 text-xs text-gray-500">The system picked {{ count($sample) }} students at random. Tick each book the student shows you; leave it unticked if they cannot.</p>
        @forelse ($students as $student)
        <div class="py-2 border-b border-gray-100 dark:border-gray-700" wire:key="sample-{{ $student->id }}">
            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                {{ $student->name }} <span class="text-xs text-gray-500">{{ $student->student_no }} · Grade {{ $student->grade->name ?? '' }}{{ $student->section ? ' ' . $student->section : '' }}</span>
            </p>
            <div class="flex flex-wrap gap-x-5 gap-y-1 mt-1">
                @foreach ($student->heldBooks as $issue)
                <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-200">
                    <input type="checkbox" wire:model.defer="seen.{{ $student->id }}.{{ $issue->manual_book_id }}" class="text-blue-600 border-gray-300 rounded">
                    {{ $issue->book->title ?? '' }}
                </label>
                @endforeach
            </div>
        </div>
        @empty
        <p class="text-sm text-gray-500">No students at this school hold books in the system yet.</p>
        @endforelse
    </div>

    <div class="p-5 card">
        <x-jet-label for="visit-notes" value="Notes (storage conditions, who was present, follow-up needed)" />
        <textarea wire:model.defer="notes" id="visit-notes" rows="3"
            class="mt-1 form-control"></textarea>
        <div class="flex justify-end gap-3 mt-4">
            <a href="{{ route('manual-tracking.visits.index') }}" class="px-4 py-2 text-sm text-gray-700 bg-gray-100 rounded-md dark:bg-gray-700 dark:text-gray-200">Cancel</a>
            <button type="submit" wire:loading.attr="disabled" class="btn-success">Save visit</button>
        </div>
    </div>
</form>
