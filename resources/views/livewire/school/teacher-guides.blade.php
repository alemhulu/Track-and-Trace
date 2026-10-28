<div class="space-y-4">
    <form wire:submit.prevent="issue" class="grid grid-cols-1 gap-3 p-5 bg-white rounded-lg shadow-sm md:grid-cols-5 dark:bg-gray-800">
        <div class="md:col-span-2">
            <x-jet-label for="guide-title" value="Title" />
            <x-form.select wire:model.defer="form.manual_book_id" id="guide-title">
                <option value="">Select title</option>
                @foreach ($titles as $title)
                <option value="{{ $title->id }}">{{ $title->title }} ({{ (int) ($onHand[$title->id] ?? 0) }} on hand)</option>
                @endforeach
            </x-form.select>
        </div>
        <div>
            <x-jet-label for="guide-teacher" value="Teacher" />
            <x-jet-input wire:model.defer="form.teacher_name" id="guide-teacher" type="text" class="block w-full mt-1" />
            <x-jet-input-error for="form.teacher_name" class="mt-1" />
        </div>
        <div>
            <x-jet-label for="guide-section" value="Section" />
            <x-jet-input wire:model.defer="form.section" id="guide-section" type="text" placeholder="A" class="block w-full mt-1" />
        </div>
        <div class="flex items-end gap-2">
            <div class="w-20">
                <x-jet-label for="guide-qty" value="Copies" />
                <x-jet-input wire:model.defer="form.quantity" id="guide-qty" type="number" min="1" class="block w-full mt-1" />
            </div>
            <button type="submit" class="btn-success">Issue</button>
        </div>
    </form>

    <div class="overflow-x-auto card">
        <table class="table-ui">
            <thead>
                <tr>
                    <th class="px-4 py-2">Teacher</th>
                    <th class="px-4 py-2">Title</th>
                    <th class="px-4 py-2">Section</th>
                    <th class="px-4 py-2">Copies</th>
                    <th class="px-4 py-2">Issued</th>
                    <th class="px-4 py-2">Returned</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($issues as $issue)
                <tr wire:key="guide-{{ $issue->id }}">
                    <td class="px-4 py-2 font-medium text-gray-900 dark:text-gray-100">{{ $issue->teacher_name }}</td>
                    <td class="px-4 py-2 text-gray-700 dark:text-gray-300">{{ $issue->book->title ?? '—' }}</td>
                    <td class="px-4 py-2 text-gray-700 dark:text-gray-300">{{ $issue->section ?: '—' }}</td>
                    <td class="px-4 py-2 text-gray-700 dark:text-gray-300">{{ $issue->quantity }}</td>
                    <td class="px-4 py-2 text-gray-700 dark:text-gray-300">{{ $issue->issued_at->format('Y-m-d') }}</td>
                    <td class="px-4 py-2">
                        @if ($issue->returned_at)
                        <span class="text-gray-500">{{ $issue->returned_at->format('Y-m-d') }}</span>
                        @else
                        <button type="button" wire:click="returnGuides({{ $issue->id }})" class="text-xs link">Record return</button>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-4 py-6 text-center text-gray-500">No teacher guides issued this year.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
