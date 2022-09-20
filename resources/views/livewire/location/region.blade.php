<div class="lg:grid lg:grid-cols-12 lg:gap-x-5">
    <aside class="py-6 px-5 sm:px-6 lg:py-0 lg:px-0 lg:col-span-4 bg-white dark:bg-gray-800 sm:rounded-md">
        <x-form.card function="addRegion" title="Add New Region / City">
            <div>
                <x-jet-label for="country_id" value=" {{__('Select Country')}}" />
                <x-form.select wire:model="country_id" id="country_id">
                    <option value=""> {{__('Country') }}</option>
                    @foreach ($countries as $country)
                    <option value="{{ $country->id }}">{{ $country->name }}</option>
                    @endforeach
                </x-form.select>
                <x-jet-input-error for="country_id" alert="Select Country" />
            </div>

            <div>
                <x-jet-label for="is_city" value="{{ __('Type') }}" />
                <div class="flex justify-between space-x-3">
                    <x-form.radio value="0" type="text" wire:model.defer="is_city" label="Region" name="is_city"
                        id="active" />

                    <x-form.radio value="1" type="text" wire:model.defer="is_city" label="City" name="is_city"
                        id="inactive" />
                </div>
                <x-jet-input-error for="is_city" alert="Region or City Type" />
            </div>

            <div>
                <x-jet-label for="name" value="{{ __('Region / City') }}" />
                <x-jet-input type="text" wire:model.defer="name" placeholder="Type Rgion or City Name"
                    class="block w-full" />
                <x-jet-input-error for="name" alert="Rgion or City Name" />
            </div>
        </x-form.card>
    </aside>

    <x-form.table title="Country List">
        <x-slot name="tableHeaders">
            <x-data-table.th scope="col">#</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Region City') }}</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Type') }}</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Country') }}</x-data-table.th>
            <x-data-table.th scope="col" class="sr-only">{{__('Action') }}</x-data-table.th>
        </x-slot>

        <x-slot name="tableRows">
            @php $i = 1; @endphp
            @forelse($regions as $record)
            <x-data-table.tr>
                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-700 dark:text-gray-100">{{$i++}}</div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-700 dark:text-gray-100 font-semibold">{{$record->name}}</div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-700 dark:text-gray-100">
                        {{$record->is_city == 1 ? 'City' : 'Region'}}
                    </div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-700 dark:text-gray-100">{{$record->Country->name}}</div>
                </td>

                <td class="px-5 py-2">
                    <x-action.table-button id="{{$record->id}}" edit="editRegion" delete="deleteRegion" />
                </td>
            </x-data-table.tr>
            @empty
            <x-data-table.empty colspan=5 />
            @endforelse
        </x-slot>
        {{ $regions->links() }}
    </x-form.table>

    <livewire:location.modal.edit-region>
</div>