<div>
    <aside class="px-5 py-6 bg-white sm:px-6 lg:py-0 lg:px-0 lg:col-span-4 dark:bg-gray-800 sm:rounded-md">
        <x-form.card function="addGrade" title="{{ $editingGradeId ? 'Edit Grade' : 'Add New Grade' }}"
            submitLabel="{{ $editingGradeId ? 'Update' : 'Add' }}">
            <div>
                <x-jet-label for="name" value="{{ __('Name') }}" />
                <x-jet-input type="text" wire:model.defer="name" placeholder="Grade Name" />
            </div>

            <div>
                <x-jet-label for="departmentIds">
                    Subject <span class="text-gray-400"> / Select One or More</span>
                </x-jet-label>
                <div class="flex flex-wrap gap-2 p-4 mt-1 font-semibold border rounded-md border-lime-500">
                    @foreach ($subjects as $subject)
                    <x-form.multi-check-box name="{{ $subject->name }}" model="subjectIds" value="{{ $subject->id }}" />
                    @endforeach
                </div>
                <x-jet-input-error for="subjectIds" alert="Subjects" />
            </div>

            <div>
                <x-jet-label for="code" value="{{ __('Code') }}" />
                <x-jet-input type="text" wire:model.defer="code" placeholder="Type Code" />
                <x-jet-input-error for="code" alert="Grade Code" />
            </div>

            <div>
                <x-jet-label for="description" value="{{ __('Description') }}" />
                <x-form.textarea name="description" wire:model.defer="description" placeholder="Type Description"
                    row="3" />
                <x-jet-input-error for="description" alert="Grade Description" />
            </div>
        </x-form.card>
    </aside>

    <x-form.table title="Grade List">
        <x-slot name="tableHeaders">
            {{-- <x-data-table.th scope="col">#</x-data-table.th> --}}
            <x-data-table.th scope="col"> {{__('Name') }}</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Subject') }}</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Code') }}</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Description') }}</x-data-table.th>
            <x-data-table.th scope="col" class="sr-only">{{__('Action') }}</x-data-table.th>
        </x-slot>

        <x-slot name="tableRows">
            <?php  $i=1;   ?>

            @forelse($gradeList as $record)
            <x-data-table.tr>
                {{-- <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-900 dark:text-gray-100">{{$i++}}
</div>
</td> --}}

<td class="px-5 py-2 whitespace-nowrap">
    <div class="text-sm text-gray-900 dark:text-gray-100">{{$record->name}}</div>
</td>

<td class="px-5 py-2 whitespace-nowrap">
    <div class="max-w-md text-sm text-gray-900 truncate dark:text-gray-100">
        {{ $record->subjects->pluck('name')->join(', ') ?: '---' }}
    </div>
</td>

<td class="px-5 py-2 whitespace-nowrap">
    <div class="text-sm text-gray-900 dark:text-gray-100">{{$record->code}}</div>
</td>

<td class="px-5 py-2 whitespace-nowrap">
    <div class="text-sm text-gray-900 dark:text-gray-100">{{$record->description}}</div>
</td>

<td class="px-5 py-2">
    <x-action.table-button id="{{$record->id}}" edit="editGrade" delete="deleteGrade">
    </x-action.table-button>
</td>
</x-data-table.tr>
@empty
<x-data-table.empty colspan=6 />
@endforelse
</x-slot>
</x-form.table>
</div>
