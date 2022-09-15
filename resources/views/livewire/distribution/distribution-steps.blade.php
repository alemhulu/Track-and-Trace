<div>
    <div class=" border-t border-gray-200 pt-3 mt-5">
        <h3 class="text-lg leading-6 font-medium text-gray-900">
            Distribution Routes and Steps
        </h3>
        <p class="mt-1 max-w-2xl text-sm text-gray-500">
            Distribution Steps information
        </p>
    </div>

    <x-form.table title="Distribution Steps & Route" :search=false :entries=false>
        <x-slot name="tableHeaders">
            <x-data-table.th scope="col">{{ __('Steps') }}</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Routes') }}</x-data-table.th>
            <x-data-table.th scope="col"> {{__('From') }}</x-data-table.th>
            <x-data-table.th scope="col"> {{__('To ') }}</x-data-table.th>
            <x-data-table.th scope="col" class="sr-only">{{__('Action') }}</x-data-table.th>
        </x-slot>

        <x-slot name="tableRows">
            @php $i = 1; @endphp
            @forelse($steps as $index=>$record)
            <x-data-table.tr>
                <td class="px-5 py-2 whitespace-nowrap">
                    <div class=" rounded-full bg-blue-500 w-10 h-10 flex items-center justify-center">
                        <span class="text-sm text-blue-50 font-bold"> {{ $index + 1 }}</span>
                    </div>
                </td>

                <td class="px-5 py-2 w-80">
                    <x-form.select wire:model=" steps.{{ $index }}.route_id" id="route_id" class="max-w-xl min-w-max">
                        <option>select</option>
                        <option>Route 1</option>
                        <option>Route 2 </option>
                    </x-form.select>
                    <x-jet-input-error for="steps.{{ $index }}.route_id" alert="Route" />
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-700">WareHouse {{ $index }}</div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-700">WareHouse {{ $index + 1 }}</div>
                </td>

                <td class="px-5 py-2">
                    <x-action.table-button id="{{ $index }}" delete="deleteStep" />
                </td>
            </x-data-table.tr>
            @empty
            <x-data-table.empty colspan=5 />
            @endforelse
        </x-slot>

        <div class="ml-4">
            <x-action.table-button add="addStep" text="Add Step" />
        </div>
    </x-form.table>
</div>