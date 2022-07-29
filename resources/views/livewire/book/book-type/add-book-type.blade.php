<div>
    {{-- Close your eyes. Count to one. That is how long forever feels. --}}
    <div>
        {{-- Stop trying to control. --}}
        <aside class="py-6 px-5 sm:px-6 lg:py-0 lg:px-0 lg:col-span-4 bg-white sm:rounded-md">
            <x-form.card function="addBookType" title="Add New Book Type ">
                <div>
                    <x-jet-label for="name" value="{{ __('Name') }}" />
                    <x-jet-input type="text" wire:model.defer="name" placeholder="Book Type Name" />
                    <x-jet-input-error for="name" alert="Book Type Name" />
                </div>

                <div>
                    <x-jet-label for="code" value="{{ __('Code') }}" />
                    <x-jet-input type="text" wire:model.defer="code" placeholder="Type Code" />
                    <x-jet-input-error for="code" alert="Book Type Code" />
                </div>

                <div>
                    <x-jet-label for="description" value="{{ __('Description') }}" />
                    <x-form.textarea name="description" wire:model.defer="description" placeholder="Type Description"
                        row="3" />
                    <x-jet-input-error for="de" alert="Book Type Description" />
                </div>
            </x-form.card>
        </aside>

        <x-form.table title="Book Type List">
            <x-slot name="tableHeaders">
                <x-data-table.th scope="col">#</x-data-table.th>
                <x-data-table.th scope="col"> {{__('Name') }}</x-data-table.th>
                <x-data-table.th scope="col"> {{__('Code') }}</x-data-table.th>
                <x-data-table.th scope="col"> {{__('Description') }}</x-data-table.th>
                <x-data-table.th scope="col" class="sr-only">{{__('Action') }}</x-data-table.th>
            </x-slot>

            <x-slot name="tableRows">
                <?php  $i=1;   ?>

                @forelse($bookTypes as $record)
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

                    <td class="px-5 py-2">
                        <x-action.table-button id="{{$record->id}}" edit="editBookType" delete="deleteBookType">
                        </x-action.table-button>
                    </td>
                </x-data-table.tr>
                @empty
                <x-data-table.empty colspan=5 />
                @endforelse
            </x-slot>
            {{-- {{$bookTypes->links()}} --}}
        </x-form.table>

        <livewire:book.book-type.edit-book-type>
    </div>
</div>