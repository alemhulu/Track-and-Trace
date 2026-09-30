<div class="lg:grid lg:grid-cols-12 lg:gap-x-5">
    <aside class="py-6 px-5 sm:px-6 lg:py-0 lg:px-0 lg:col-span-4 bg-white dark:bg-gray-800 sm:rounded-md">
        <x-form.card function="addWarehouse" title="Add New Store To Wearhouse">
            <div>
                <x-jet-label for="organizaton_id" value=" {{__('Organization')}}" />
                <x-form.select wire:model="organizaton_id" id="organizaton_id">
                    <option value=""> {{__('select') }}</option>
                    @foreach ($organizations as $organization)
                    <option value="{{ $organization->id }}">{{ $organization->name }}</option>
                    @endforeach
                </x-form.select>
                <x-jet-input-error for="organizaton_id" alert="Select organization" />
            </div>

            <div>
                <x-jet-label for="warehouse_id" value=" {{__('Warehouse')}}" />
                <x-jet-input type="number" wire:model.defer="warehouse_id" id="warehouse_id" min="1"
                    placeholder="Enter branch number" class="block w-full" />
                <x-jet-input-error for="warehouse_id" alert="Select Warehouse" />
            </div>

            <div>
                <x-jet-label for="user_id" value=" {{__('Assign user')}}" />
                <x-form.select wire:model="user_id" id="user_id">
                    <option value=""> {{__('select user')}}</option>
                    @foreach ($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </x-form.select>
                <x-jet-input-error for="user_id" alert="Select user" />
            </div>

            <div>
                <x-jet-label for="name" value="{{ __('Name') }}" />
                <x-jet-input type="text" wire:model.defer="name" placeholder="Type Store Name" class="block w-full" />
                <x-jet-input-error for="name" alert="Store Name" />
            </div>

            <div>
                <x-jet-label for="description" value="{{ __('Description') }}" />
                <x-form.textarea name="description" wire:model.defer="description" placeholder="Type Description"
                    row=3 />
                <x-jet-input-error for="description" alert="Store Description" />
            </div>
        </x-form.card>
    </aside>

    <x-form.table title="Warehouse Store List">
        <x-slot name="tableHeaders">
            <x-data-table.th scope="col">#</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Name') }}</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Description') }}</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Assigned User') }}</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Organization') }}</x-data-table.th>
            <x-data-table.th scope="col" class="sr-only">{{__('Action') }}</x-data-table.th>
        </x-slot>

        <x-slot name="tableRows">
            @php $i = 1; @endphp
            @forelse($wearhouses as $record)
            <x-data-table.tr>
                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-700 dark:text-gray-100">{{$i++}}</div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-700 dark:text-gray-100 font-semibold">{{ __('Branch') }}
                        {{$record->branch}}
                    </div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-700 dark:text-gray-100">
                        {{ __('Branch') }} {{$record->branch}}
                    </div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <x-organization.contact name="{{ optional($record->user)->name ?? 'N/A' }}"
                        email="{{ optional($record->user)->email ?? 'N/A' }}"
                        phone="{{ optional($record->user)->phone ?? 'N/A' }}" />
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <x-organization.info image="logom.png" name="{{ optional($record->organization)->name ?? 'N/A' }}"
                        email="{{ optional($record->organization)->email ?? 'N/A' }}"
                        phone="{{ optional($record->organization)->phone ?? 'N/A' }}" />
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
