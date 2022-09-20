<div class="lg:grid lg:grid-cols-12 lg:gap-x-5">
    <aside class="py-6 px-5 sm:px-6 lg:py-0 lg:px-0 lg:col-span-4 bg-white dark:bg-gray-800 sm:rounded-md">
        <x-form.card function="addstore" title="Add New Store To Wearhouse">
            <div>
                <x-jet-label for="organizaton_id" value=" {{__('Organization')}}" />
                <x-form.select wire:model="organizaton_id" id="organizaton_id">
                    <option value=""> {{__('select') }}</option>
                    {{-- @foreach ($organizations as $organization)
                    <option value="{{ $organization->id }}">{{ $organization->name }}</option>
                    @endforeach --}}
                </x-form.select>
                <x-jet-input-error for="organizaton_id" alert="Select organization" />
            </div>

            <div>
                <x-jet-label for="store_id" value=" {{__('Store')}}" />
                <x-form.select wire:model="store_id" id="store_id">
                    <option value=""> {{__('select') }}</option>
                    {{-- @foreach ($stores as $store)
                    <option value="{{ $store->id }}">{{ $store->name }}</option>
                    @endforeach --}}
                </x-form.select>
                <x-jet-input-error for="store_id" alert="Select store" />
            </div>

            <div>
                <x-jet-label for="user_id" value=" {{__('Assign user')}}" />
                <x-user.select :users="$users" :select="$select" :hide="$hide" :user="$user" />
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
                <x-jet-input-error for="de" alert="Store Description" />
            </div>
        </x-form.card>
    </aside>

    <x-form.table title="Store Store List">
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
            @forelse($stores as $record)
            <x-data-table.tr>
                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-700 dark:text-gray-100">{{$i++}}</div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-700 dark:text-gray-100 font-semibold">{{$record->name}}</div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-700 dark:text-gray-100">
                        {{$record->decription == 1 ? 'City' : 'Region'}}
                    </div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <x-organization.contact name="Abebe Kebede" email="abe@kebede.com" phone="0987654312" />
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <x-organization.info image="logom.png" name="Ministry of Education" email="mail@ministry.com"
                        phone="0987654321" />
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