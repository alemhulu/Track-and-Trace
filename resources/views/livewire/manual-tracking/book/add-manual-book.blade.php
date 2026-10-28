<div>
    <x-form.card function="saveBook" title="{{ $editingBookId ? 'Edit Title' : 'Add Title' }}"
        submitLabel="{{ $editingBookId ? 'Update' : 'Add' }}">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <x-jet-label for="region_id" value="Region" />
                <x-form.select wire:model="region_id" id="region_id">
                    <option value="">Select region</option>
                    @foreach ($regions as $region)
                    <option value="{{ $region->id }}">{{ $region->name }}</option>
                    @endforeach
                </x-form.select>
            </div>

            <div>
                <x-jet-label for="grade_id" value="Grade" />
                <x-form.select wire:model="grade_id" id="grade_id">
                    <option value="">Select grade</option>
                    @foreach ($grades as $grade)
                    <option value="{{ $grade->id }}">Grade {{ $grade->name }}</option>
                    @endforeach
                </x-form.select>
            </div>

            <div>
                <x-jet-label for="subject_id" value="Subject" />
                <x-form.select wire:model="subject_id" id="subject_id">
                    <option value="">Select subject</option>
                    @foreach ($subjects as $subject)
                    <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                    @endforeach
                </x-form.select>
            </div>

            <div class="md:col-span-2">
                <x-jet-label for="title" value="Title" />
                <x-jet-input wire:model.defer="title" id="title" type="text" class="mt-1 block w-full" />
                <x-jet-input-error for="title" class="mt-2" />
            </div>

            <div>
                <x-jet-label for="code" value="Internal Code" />
                <x-jet-input wire:model.defer="code" id="code" type="text" class="mt-1 block w-full" />
                <x-jet-input-error for="code" class="mt-2" />
            </div>

            <div>
                <x-jet-label for="students_per_package" value="Student Books per Package" />
                <x-jet-input wire:model.defer="students_per_package" id="students_per_package" type="number" min="1"
                    class="mt-1 block w-full" />
                <x-jet-input-error for="students_per_package" class="mt-2" />
            </div>

            <div class="flex items-end pb-2">
                <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-200">
                    <input type="checkbox" wire:model="has_teacher_guide"
                        class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                    Each package includes 1 teacher guide
                </label>
            </div>

            <div>
                <x-jet-label for="isbn" value="ISBN" />
                <x-jet-input wire:model.defer="isbn" id="isbn" type="text" class="mt-1 block w-full" />
                <x-jet-input-error for="isbn" class="mt-2" />
            </div>
        </div>

        <div class="mt-6">
            <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-100">Printed QR codes</h4>
            <p class="text-xs text-gray-500 dark:text-gray-400">
                Enter the exact text a scanner reads from the book's QR code, and the code printed below it.
            </p>

            <div class="grid grid-cols-1 gap-4 mt-3 md:grid-cols-2">
                @foreach (\App\Models\ManualTracking\ManualBook::EDITIONS as $edition => $label)
                @if ($edition === 'student' || $has_teacher_guide)
                <div class="p-4 border border-gray-200 rounded-lg dark:border-gray-700">
                    <p class="mb-2 text-sm font-medium text-gray-700 dark:text-gray-200">{{ $label }}</p>
                    <x-jet-label for="codes-{{ $edition }}-payload" value="QR text" />
                    <x-jet-input wire:model.defer="codes.{{ $edition }}.payload" id="codes-{{ $edition }}-payload"
                        type="text" class="mt-1 block w-full" />
                    <x-jet-input-error for="codes.{{ $edition }}.payload" class="mt-2" />

                    <x-jet-label for="codes-{{ $edition }}-print" value="Printed code" class="mt-3" />
                    <x-jet-input wire:model.defer="codes.{{ $edition }}.print_code" id="codes-{{ $edition }}-print"
                        type="text" placeholder="e.g. 25110-014-1-1709" class="mt-1 block w-full" />
                </div>
                @endif
                @endforeach
            </div>
        </div>

        <div class="mt-4">
            <x-jet-label for="notes" value="Notes" />
            <textarea wire:model.defer="notes" id="notes" rows="3"
                class="mt-1 form-control"></textarea>
            <x-jet-input-error for="notes" class="mt-2" />
        </div>
    </x-form.card>
</div>
