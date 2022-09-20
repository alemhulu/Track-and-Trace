<div>
    {{-- Stop trying to control. --}}
    <aside class="py-6 px-5 sm:px-6 lg:py-0 lg:px-0 lg:col-span-4 bg-white dark:bg-gray-800 sm:rounded-md">
        <x-form.card function="addGrade" title="Add New Grade ">
            <div>
                <x-jet-label for="name" value="{{ __('Name') }}" />
                <x-jet-input type="text" wire:model.defer="name" placeholder="Grade Name" />
                <x-jet-input-error for="name" alert="Grade Name" />
            </div>


            <div>
                <x-jet-label for="departmentIds">
                    Subject <span class="text-gray-400"> / Select One or More</span>
                </x-jet-label>
                <div class="mt-1 grid grid-cols-10 gap-2 p-4 border border-lime-500 rounded-md font-semibold">
                    <x-form.multi-check-box name="Biology" model="subjectId" value='1' />
                    <x-form.multi-check-box name="Chemistry" model="subjectId" value='1' />
                    <x-form.multi-check-box name="Maths" model="subjectId" value='1' />
                    <x-form.multi-check-box name="Physics" model="subjectId" value='1' />
                    <x-form.multi-check-box name="English" model="subjectId" value='1' />
                    <x-form.multi-check-box name="Amharic" model="subjectId" value='1' />
                    <x-form.multi-check-box name="Civic" model="subjectId" value='1' />
                    <x-form.multi-check-box name="History" model="subjectId" value='1' />
                    <x-form.multi-check-box name="Geography" model="subjectId" value='1' />
                    <x-form.multi-check-box name="ICT" model="subjectId" value='1' />
                </div>
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
                <x-jet-input-error for="de" alert="Grade Description" />
            </div>
        </x-form.card>
    </aside>

    <x-form.table title="Grade List">
        <x-slot name="tableHeaders">
            <x-data-table.th scope="col">#</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Name') }}</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Subject') }}</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Code') }}</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Description') }}</x-data-table.th>
            <x-data-table.th scope="col" class="sr-only">{{__('Action') }}</x-data-table.th>
        </x-slot>

        <x-slot name="tableRows">
            <?php  $i=1;   ?>

            @forelse($grades as $record)
            <x-data-table.tr>
                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-900 dark:text-gray-100">{{$i++}}</div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-900 dark:text-gray-100">{{$record->name}}</div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-900 dark:text-gray-100">{{$record->subject}}</div>
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
        {{-- {{$grades->links()}} --}}
    </x-form.table>

    {{-- <livewire:book.grade.edit-grade> --}}
</div>