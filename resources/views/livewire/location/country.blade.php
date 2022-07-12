<div class="lg:grid lg:grid-cols-12 lg:gap-x-5">
    <aside class="py-6 px-5 sm:px-6 lg:py-0 lg:px-0 lg:col-span-4 bg-white sm:rounded-md">
        <x-form.card function="addCountry" title="Add New Country">
            <div>
                <x-jet-label for="name" value="{{ __('Country') }}" />
                <x-jet-input type="text" wire:model.defer="name" placeholder="Type Country Name" />
                <x-jet-input-error for="name" alert="Country Name" />
            </div>
        </x-form.card>
    </aside>

    <x-form.table title="Country List">
        <x-slot name="tableHeaders">
            <x-data-table.th scope="col">#</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Country') }}</x-data-table.th>
            <x-data-table.th scope="col" class="sr-only">{{__('Action') }}</x-data-table.th>
        </x-slot>

        <x-slot name="tableRows">
            <?php $i=1;?>
            @forelse($countries as $record)
            <x-data-table.tr>
                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{$i++}}</div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{$record->name}}</div>
                </td>

                <td class="px-5 py-2">
                    <x-action.table-button id="{{$record->id}}" edit="editCountry" delete="deleteCountry">
                    </x-action.table-button>
                </td>
            </x-data-table.tr>
            @empty
            <x-data-table.empty colspan=3 />
            @endforelse
        </x-slot>
        {{$countries->links()}}
    </x-form.table>

    {{-- Edit country Modal --}}
    <livewire:location.modal.edit-country>
</div>