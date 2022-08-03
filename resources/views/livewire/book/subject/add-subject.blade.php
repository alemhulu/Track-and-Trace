<div>
    {{-- Stop trying to control. --}}
    <aside class="py-6 px-5 sm:px-6 lg:py-0 lg:px-0 lg:col-span-4 bg-white sm:rounded-md">
        <x-form.card function="addSubject" title="Add New Subject">
            <div>
                <x-jet-label for="name" value="{{ __('Name') }}" />
                <x-jet-input type="text" wire:model.defer="name" placeholder="Subject Name" />
                <x-jet-input-error for="name" alert="Subject Name" />
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
                <x-jet-input-error for="de" alert="Subject Description" />
            </div>
        </x-form.card>
    </aside>

    <x-form.table title="Grade List">
        <x-slot name="tableHeaders">
            <x-data-table.th scope="col">#</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Name') }}</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Code') }}</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Description') }}</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Grade') }}</x-data-table.th>
            <x-data-table.th scope="col" class="sr-only">{{__('Action') }}</x-data-table.th>
        </x-slot>

        <x-slot name="tableRows">
            <?php  $i=1;   ?>

            @forelse($subjects as $record)
            <x-data-table.tr>
                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{$i++}}</div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{$record->name}}</div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{$record->code}}</div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{$record->description}}</div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{$record->grade}}</div>
                </td>

                <td class="px-5 py-2">
                    <x-action.table-button id="{{$record->id}}" edit="editSubject" delete="deleteSubject">
                    </x-action.table-button>
                </td>
            </x-data-table.tr>
            @empty
            <x-data-table.empty colspan=6 />
            @endforelse
        </x-slot>
        {{-- {{$subjects->links()}} --}}
    </x-form.table>

    <livewire:book.subject.edit-subject>
</div>