<div>
    <x-form.table title="Packages List" search="">
        <x-slot name="tableHeaders">
            <x-data-table.th scope="col">#</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Package') }}</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Books') }}</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Status') }}</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Check') }}</x-data-table.th>
            <x-data-table.th scope="col" class="sr-only">{{__('Action') }}</x-data-table.th>
        </x-slot>

        <x-slot name="tableRows">
            @php $i = 1; $record = 50; $start = 100000 ;@endphp
            {{-- @forelse($books as $record) --}}
            @for($i; $i <=$record; $i++) <x-data-table.tr>
                <td class="px-5 py-2 ">
                    <div class="text-lg text-gray-500 dark:text-gray-100 font-bold">{{ $i }}</div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <x-stat.package batch="POB10387618" qr="{{ $start + $i }}" />
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <x-stat.book-info grade="Grade 12" quantity="41" range="7235662 - 7235134" />
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <x-button btnType="secondary" class="">Unchecked</x-button>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <x-form.toggle />
                </td>

                <td class="px-5 py-2">
                    <x-action.table-button id="{{ $record }}" view="viewRequestInfo" />
                </td>
                </x-data-table.tr>
                @endfor
                {{-- @empty --}}
                {{-- <x-data-table.empty colspan=6 /> --}}
                {{-- @endforelse --}}
        </x-slot>
    </x-form.table>
</div>