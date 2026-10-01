<div>
    <aside class="px-5 py-6 bg-white sm:px-6 lg:py-0 lg:px-0 lg:col-span-4 dark:bg-gray-800 sm:rounded-md">
        <x-form.card function="addPaperSize"
            title="{{ $editingPaperSizeId ? 'Edit Paper Size' : 'Add New Paper Size' }}"
            submitLabel="{{ $editingPaperSizeId ? 'Update' : 'Add' }}">
            <div>
                <x-jet-label for="name" value="{{ __('Name') }}" />
                <x-jet-input type="text" wire:model.defer="name" placeholder="Paper Size Name" />
            </div>

            <div>
                <x-jet-label for="code" value="{{ __('Code') }}" />
                <x-jet-input type="text" wire:model.defer="code" placeholder="Type Code" />
                <x-jet-input-error for="code" alert="Paper Size Code" />
            </div>

            <div>
                <x-jet-label for="width" value="{{ __('Width') }}" />
                <x-jet-input type="number" wire:model.defer="width" placeholder="Type Width" />
                <x-jet-input-error for="width" alert="Paper Size Width" />
            </div>

            <div>
                <x-jet-label for="height" value="{{ __('Height') }}" />
                <x-jet-input type="number" wire:model.defer="height" placeholder="Type Height" />
                <x-jet-input-error for="height" alert="Paper Size Height" />
            </div>

        </x-form.card>
    </aside>

    <x-form.table title="Paper Size List">
        <x-slot name="tableHeaders">
            <x-data-table.th scope="col">#</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Name') }}</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Code') }}</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Size') }}</x-data-table.th>
            <x-data-table.th scope="col" class="sr-only">{{__('Action') }}</x-data-table.th>
        </x-slot>

        <x-slot name="tableRows">
            <?php  $i=1;   ?>

            @forelse($paperSizes as $record)
            <x-data-table.tr>
                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-900 dark:text-gray-100">{{$i++}}</div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-900 dark:text-gray-100">{{$record->name}}</div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-900 dark:text-gray-100">{{$record->code}}</div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-900 dark:text-gray-100">{{$record->width}} x {{$record->height}}
                    </div>
                </td>

                <td class="px-5 py-2">
                    <x-action.table-button id="{{$record->id}}" edit="editPaperSize" delete="deletePaperSize">
                    </x-action.table-button>
                </td>
            </x-data-table.tr>
            @empty
            <x-data-table.empty colspan=5 />
            @endforelse
        </x-slot>
    </x-form.table>
</div>
