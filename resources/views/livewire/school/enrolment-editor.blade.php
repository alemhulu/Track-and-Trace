<form wire:submit.prevent="save" class="p-5 bg-white rounded-lg shadow-sm dark:bg-gray-800">
    <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">
        Students enrolled per grade this year. Leave a grade empty if the school does not teach it. Sections are optional; without them the system assumes up to 40 students per section.
    </p>

    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="text-xs text-gray-600 dark:text-gray-300">
                <tr>
                    <th class="px-2 py-2">Grade</th>
                    <th class="px-2 py-2">Male</th>
                    <th class="px-2 py-2">Female</th>
                    <th class="px-2 py-2">Total</th>
                    <th class="px-2 py-2">Sections</th>
                    <th class="px-2 py-2">In student register</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @foreach ($grades as $grade)
                @php
                    $total = (int) ($rows[$grade->id]['male'] ?: 0) + (int) ($rows[$grade->id]['female'] ?: 0);
                    $inRegister = (int) ($registered[$grade->id] ?? 0);
                @endphp
                <tr wire:key="grade-{{ $grade->id }}">
                    <td class="px-2 py-2 font-medium text-gray-900 dark:text-gray-100">Grade {{ $grade->name }}</td>
                    <td class="px-2 py-2"><x-jet-input wire:model.lazy="rows.{{ $grade->id }}.male" type="number" min="0" class="w-24" /></td>
                    <td class="px-2 py-2"><x-jet-input wire:model.lazy="rows.{{ $grade->id }}.female" type="number" min="0" class="w-24" /></td>
                    <td class="px-2 py-2 font-semibold text-gray-700 dark:text-gray-200">{{ $total ? number_format($total) : '—' }}</td>
                    <td class="px-2 py-2"><x-jet-input wire:model.lazy="rows.{{ $grade->id }}.sections" type="number" min="1" class="w-20" placeholder="{{ $total ? ceil($total / 40) : '' }}" /></td>
                    <td class="px-2 py-2 {{ $total && $inRegister && $inRegister !== $total ? 'text-amber-600 font-semibold' : 'text-gray-500' }}">
                        {{ $inRegister ? number_format($inRegister) : '—' }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <x-jet-input-error for="rows.*" class="mt-2" />

    <div class="flex justify-end mt-4">
        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700">Save Enrolment</button>
    </div>
</form>
