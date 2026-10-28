<div class="relative">
    <x-form.table title="Organizations List">
        <x-slot name="tableHeaders">
            <x-data-table.th scope="col">#</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Organizations') }}</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Location') }}</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Contact Person') }}</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Type') }}</x-data-table.th>
            <x-data-table.th scope="col" class="sr-only">{{__('Action') }}</x-data-table.th>
        </x-slot>

        <x-slot name="tableRows">
            @php $i = 1; @endphp
            @forelse($organizations as $record)
            <x-data-table.tr>
                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-700 dark:text-gray-100">{{$i++}}</div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <x-organization.info image="{{ $record->logo }}" name="{{ $record->name }}"
                        email="{{ $record->email }}" phone="{{ $record->mobile }}" />
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <x-organization.location country="{{ $record->country->name ?? ''}}"
                        region="{{ $record->region->name ?? ''}}" zone="{{ $record->zone->name ?? '-------' }}"
                        woreda="{{ $record->woreda->name ?? '-------' }}" />
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <x-organization.info h=12 image="{{ $record->contact->profile_photo_url ?? ''}}"
                        name="{{ $record->contact->name ?? ''}}" email="{{ $record->contact->email ?? ''}}"
                        phone="{{ $record->contact->phone ?? '-------' }}" />
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <x-button class="py-1 font-bold text-blue-900" btnType="info">
                        {{ $record->organizationType->name ?? ''}}
                    </x-button>
                    @if (($record->organizationType->name ?? null) === 'School')
                    <a href="{{ route('manual-tracking.schools.show', $record->id) }}"
                        class="block mt-1 text-xs link">School records</a>
                    @endif
                </td>

                <td class="px-5 py-2">
                    <x-action.table-button id="{{ $record->id }}" delete="deleteOrganization" delete-can="organizations.delete" />
                </td>
            </x-data-table.tr>
            @empty
            <x-data-table.empty colspan=6 />
            @endforelse
        </x-slot>
        {{$organizations->links()}}
    </x-form.table>

    <x-form.confirm name="deleteOrganization" id="{{ $deleteId }}" />

</div>
