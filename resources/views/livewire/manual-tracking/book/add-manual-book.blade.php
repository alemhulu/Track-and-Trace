<div>
    <x-form.card function="saveBook" title="{{ $editingBookId ? 'Edit Manual Book' : 'Add Manual Book' }}"
        submitLabel="{{ $editingBookId ? 'Update' : 'Add' }}">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <x-jet-label for="code" value="Code" />
                <x-jet-input wire:model.defer="code" id="code" type="text" class="mt-1 block w-full" />
                <x-jet-input-error for="code" class="mt-2" />
            </div>

            <div>
                <x-jet-label for="title" value="Title" />
                <x-jet-input wire:model.defer="title" id="title" type="text" class="mt-1 block w-full" />
                <x-jet-input-error for="title" class="mt-2" />
            </div>

            <div>
                <x-jet-label for="grade_name" value="Grade" />
                <x-jet-input wire:model.defer="grade_name" id="grade_name" type="text" class="mt-1 block w-full" />
                <x-jet-input-error for="grade_name" class="mt-2" />
            </div>

            <div>
                <x-jet-label for="subject_name" value="Subject" />
                <x-jet-input wire:model.defer="subject_name" id="subject_name" type="text" class="mt-1 block w-full" />
                <x-jet-input-error for="subject_name" class="mt-2" />
            </div>

            <div>
                <x-jet-label for="isbn" value="ISBN" />
                <x-jet-input wire:model.defer="isbn" id="isbn" type="text" class="mt-1 block w-full" />
                <x-jet-input-error for="isbn" class="mt-2" />
            </div>

            <div>
                <x-jet-label for="edition" value="Edition" />
                <x-jet-input wire:model.defer="edition" id="edition" type="text" class="mt-1 block w-full" />
                <x-jet-input-error for="edition" class="mt-2" />
            </div>

            <div>
                <x-jet-label for="total_copies" value="Number of Copies" />
                <x-jet-input wire:model.defer="total_copies" id="total_copies" type="number" min="0"
                    class="mt-1 block w-full" />
                <x-jet-input-error for="total_copies" class="mt-2" />
            </div>

            <div class="md:col-span-2">
                <x-jet-label for="notes" value="Notes" />
                <textarea wire:model.defer="notes" id="notes" rows="3"
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100"></textarea>
                <x-jet-input-error for="notes" class="mt-2" />
            </div>
        </div>
    </x-form.card>
</div>
