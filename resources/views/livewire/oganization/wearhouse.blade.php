<div class="lg:grid lg:grid-cols-12 lg:gap-x-5">
    <aside class="py-6 px-5 sm:px-6 lg:py-0 lg:px-0 lg:col-span-4 bg-white sm:rounded-md">
        <x-form.card function="addWarehouse" title="Add New Wearhouse">
            <div>
                <x-jet-label for="organizaton_id" value=" {{__('Oganization')}}" />
                <x-form.select wire:model="organizaton_id" id="organizaton_id">
                    <option value=""> {{__('Organization') }}</option>
                    {{-- @foreach ($oganizations as $oganization)
                    <option value="{{ $oganization->id }}">{{ $oganization->name }}</option>
                    @endforeach --}}
                </x-form.select>
                <x-jet-input-error for="organizaton_id" alert="Select Oganization" />
            </div>

            <div>
                <x-jet-label for="name" value="{{ __('Name') }}" />
                <x-jet-input type="text" wire:model.defer="name" placeholder="Type Wearhouse Name"
                    class="block w-full" />
                <x-jet-input-error for="name" alert="Wearhouse Name" />
            </div>

            <div>
                <x-jet-label for="description" value="{{ __('Description') }}" />
                <x-form.textarea name="description" wire:model.defer="description" placeholder="Type Description"
                    row=3 />
                <x-jet-input-error for="de" alert="Wearhouse Description" />
            </div>

        </x-form.card>
    </aside>

    <x-form.table title="Wearhouse List">
        <x-slot name="tableHeaders">
            <x-data-table.th scope="col">#</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Name') }}</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Description') }}</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Organization') }}</x-data-table.th>
            <x-data-table.th scope="col" class="sr-only">{{__('Action') }}</x-data-table.th>
        </x-slot>

        <x-slot name="tableRows">
            @php $i = 1; @endphp
            @forelse($wearhouses as $record)
            <x-data-table.tr>
                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-700">{{$i++}}</div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-700 font-semibold">{{$record->name}}</div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-700">
                        {{$record->is_city == 1 ? 'City' : 'Region'}}
                    </div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-700">{{$record->Country->name}}</div>
                </td>

                <td class="px-5 py-2">
                    <x-action.table-button id="{{$record->id}}" edit="editWearhouse" delete="deleteWearhouse" />
                </td>
            </x-data-table.tr>
            @empty
            <x-data-table.empty colspan=5 />
            @endforelse
        </x-slot>
        {{-- {{ $wearhouses->links() }} --}}
    </x-form.table>

    <livewire:oganization.modal.edit-wearhouse>
</div>