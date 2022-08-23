<div>
    <section class="bg-white rounded-lg">
        <div class="max-w-screen-xl px-4 py-5 mx-auto sm:px-6 lg:px-8 mb-3">
            <div class="mt-4">
                <dl class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div class="flex flex-col px-4 py-8 text-center border border-blue-200 rounded-lg">
                        <dt class="order-last text-lg font-medium text-gray-500">
                            Total Wearhouses
                        </dt>

                        <dd class="text-4xl font-extrabold text-blue-600 md:text-5xl">
                            700
                        </dd>
                    </div>

                    <div class="flex flex-col px-4 py-8 text-center border border-blue-200 rounded-lg">
                        <dt class="order-last text-lg font-medium text-gray-500">
                            Total Stores
                        </dt>

                        <dd class="text-4xl font-extrabold text-blue-600 md:text-5xl">2.4K</dd>
                    </div>

                    <div class="flex flex-col px-4 py-8 text-center border border-blue-200 rounded-lg">
                        <dt class="order-last text-lg font-medium text-gray-500">
                            Total Books in Stores
                        </dt>

                        <dd class="text-4xl font-extrabold text-blue-600 md:text-5xl">2.1M</dd>
                    </div>
                </dl>
            </div>
        </div>
    </section>

    <x-form.table title="Wearhouse List">
        <x-slot name="tableHeaders">
            <x-data-table.th scope="col">#</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Name') }}</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Description') }}</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Organization') }}</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Conact Person') }}</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Stores') }}</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Books In Wearhouse') }}</x-data-table.th>
            <x-data-table.th scope="col" class="sr-only">{{__('Action') }}</x-data-table.th>
        </x-slot>

        <x-slot name="tableRows">
            @php $i = 1; $record = 1;@endphp
            {{-- @forelse($books as $record) --}}
            <x-data-table.tr>
                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-700">1</div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-md font-semibold text-gray-600">MoE Wearhouse 1</div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-700 whitespace-pre-line">Ministry of Education Wearhouse located at
                        around CMC</div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <x-organization.info image="logom.png" name="Ministry of Education" email="mail@ministry.com"
                        phone="0987654321" />
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <x-organization.contact name="Abebe Kebede" email="abe@kebede.com" phone="0987654312" />
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-lg text-gray-600 font-semibold">5</div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <div class="text-lg text-gray-600 font-semibold">200K+</div>
                </td>

                <td class="px-5 py-2">
                    <x-action.table-button id="{{ $record }}" view="viewBook" edit="editBook" delete="deleteBook" />
                </td>
            </x-data-table.tr>
            {{-- @empty --}}
            {{-- <x-data-table.empty colspan=6 /> --}}
            {{-- @endforelse --}}
        </x-slot>
    </x-form.table>
</div>