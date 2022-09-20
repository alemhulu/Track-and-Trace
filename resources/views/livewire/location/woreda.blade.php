<div class="lg:grid lg:grid-cols-12 lg:gap-x-5">
    <aside class="py-6 px-5 sm:px-6 lg:py-0 lg:px-0 lg:col-span-4 bg-white dark:bg-gray-800 sm:rounded-md">
        <x-form.card function="addWoreda" title="Add New Woreda">
            <div>
                <x-jet-label for="country_id" value=" {{__('Country')}}" />
                <x-form.select wire:model="country_id" id="country_id" class="block w-full">
                    <option value=""> {{__('Select Country') }}</option>
                    @foreach ($countries as $country)
                    <option value={{ $country->id }}>{{ $country->name }}</option>
                    @endforeach
                </x-form.select>
                <x-jet-input-error for="country_id" alert="Select Country" />
            </div>

            <div>
                <x-jet-label for="region_id" value=" {{__('Region / City')}}" />
                <x-form.select wire:model="region_id" id="region_id" class="block w-full">
                    <option value=""> {{__('Select Region') }}</option>
                    @foreach ($regions as $region)
                    <option value={{ $region->id }}>{{ $region->name }}</option>
                    @endforeach
                </x-form.select>
                <x-jet-input-error for="region_id" alert="Select Region" />
            </div>

            <div>
                <x-jet-label for="zone_id" value=" {{__('Zone / Sub-City')}}" />
                <x-form.select wire:model="zone_id" id="zone_id" class="block w-full">
                    <option value=""> {{__('Select Zone') }}</option>
                    @foreach ($zones as $zone)
                    <option value={{ $zone->id }}>{{ $zone->name }}</option>
                    @endforeach
                </x-form.select>
                <x-jet-input-error for="zone_id" alert="Select Zone" />
            </div>

            <div>
                <x-jet-label for="name" value="{{ __('Woreda') }}" />
                <x-jet-input type="text" wire:model.defer="name" placeholder="Type Woreda Name" class="block w-full" />
                <x-jet-input-error for="name" alert="Woreda Name" />
            </div>
        </x-form.card>
    </aside>

    <x-form.table title="Country List">
        <x-slot name="tableHeaders">
            <x-data-table.th scope="col">#</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Woreda') }}</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Zone') }}</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Region') }}</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Country') }}</x-data-table.th>
            <x-data-table.th scope="col" class="sr-only">{{__('Action') }}</x-data-table.th>
        </x-slot>

        <x-slot name="tableRows">
            @php $i = 1; @endphp
            @forelse($woredas as $record)
            <x-data-table.tr>
                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-900 dark:text-gray-100">{{$i++}}</div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-900 dark:text-gray-100">{{$record->name}}</div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-900 dark:text-gray-100">
                        {{$record->zone->name ?? '----'}}
                    </div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-900 dark:text-gray-100">{{$record->region->name}}</div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-900 dark:text-gray-100">{{$record->country->name}}</div>
                </td>

                <td class="px-5 py-2">
                    <x-action.table-button id="{{ $record->id }}" edit="editWoreda" delete="deleteWoreda" />
                </td>
            </x-data-table.tr>
            @empty
            <x-data-table.empty colspan=6 />
            @endforelse
        </x-slot>
    </x-form.table>
    <livewire:location.modal.edit-woreda>
</div>