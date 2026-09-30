@props(['steps'])

<div>
    <x-form.table title="Book Distribution Information List" :entries=false :search=false>
        <x-slot name="tableHeaders">
            <x-data-table.th scope="col">Steps</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Route') }}</x-data-table.th>
            <x-data-table.th scope="col"> {{__('From ') }}</x-data-table.th>
            <x-data-table.th scope="col"> {{__('To') }}</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Status') }}</x-data-table.th>
        </x-slot>

        <x-slot name="tableRows">
            @forelse($steps as $step)
            @php
            $route = $step->route;
            $fromWarehouse = optional($route)->fromWarehouse;
            $toWarehouse = optional($route)->toWarehouse;
            $fromOrganization = optional($fromWarehouse)->organization;
            $toOrganization = optional($toWarehouse)->organization;
            @endphp
            <x-data-table.tr>
                <td class="px-5 py-2">
                    <div class="text-sm text-gray-700 dark:text-gray-100">
                        <div class=" rounded-full bg-blue-600 w-10 h-10 flex items-center justify-center">
                            <span class="text-sm text-blue-50 font-bold">{{ $step->step_order }}</span>
                        </div>
                    </div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-600 font-semibold dark:text-gray-300">
                        {{ optional($route)->name ?? 'N/A' }}
                    </div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <x-trace.from-info :company="optional($fromOrganization)->name ?? 'N/A'"
                        :warehouse="'Branch '.(optional($fromWarehouse)->branch ?? 'N/A')" stock="-"
                        :email="optional($fromOrganization)->email ?? '-'"
                        :phone="optional($fromOrganization)->phone ?? '-'" />
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <x-trace.to-info :company="optional($toOrganization)->name ?? 'N/A'"
                        :warehouse="'Branch '.(optional($toWarehouse)->branch ?? 'N/A')" stock="-"
                        :email="optional($toOrganization)->email ?? '-'"
                        :phone="optional($toOrganization)->phone ?? '-'" />
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <x-button btnType="success" class="py-1 relative pl-6 pr-2 font-bold">
                        <i class="fi fi-rr-checkbox flex inset-0 top-1 left-1 absolute text-base"></i>Configured
                    </x-button>
                </td>
            </x-data-table.tr>
            @empty
            <x-data-table.empty colspan=5 />
            @endforelse
        </x-slot>
    </x-form.table>
</div>
