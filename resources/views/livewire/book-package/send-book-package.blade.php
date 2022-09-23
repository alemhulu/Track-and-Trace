<div>
    <x-form.table title="Request List">
        <x-slot name="tableHeaders">
            <x-data-table.th scope="col">#</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Name') }}</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Package') }}</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Organization') }}</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Conact Person') }}</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Request Date') }}</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Status') }}</x-data-table.th>
            <x-data-table.th scope="col" class="sr-only">{{__('Action') }}</x-data-table.th>
        </x-slot>

        <x-slot name="tableRows">
            @php $i = 1; $record = 1;@endphp
            {{-- @forelse($books as $record) --}}
            <x-data-table.tr>
                <td class="px-5 py-2 ">
                    <div class="text-lg text-gray-500 dark:text-gray-100 font-bold">1</div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <x-book.book-info image="https://i.ytimg.com/vi/NNKPR6nICJI/maxresdefault.jpg" grade="Grade 10"
                        subject="Biology" type="Student Text Book" edition="1st Edition 2013" ISBN="4820715" />
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <x-stat.package-info batch="PB293457638" quantity="83" range="4524033 - 4524116" />
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <x-organization.info image="logom.png" name="Ministry of Education" email="mail@ministry.com"
                        phone="0987654321" />
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <x-organization.info h=12 image="{{ Auth::user()->profile_photo_url }}" name="Abebe Kebede"
                        email="abe@kebede.com" phone="0987654312" />
                </td>

                <td class="px-5 py-2 ">
                    <div class="text-gray-600 font-semibold dark:text-gray-300">4 days ago</div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <x-button btnType="success" class="">Received</x-button>
                </td>

                <td class="px-5 py-2">
                    <x-action.table-button id="{{ $record }}" view="viewBook" />
                </td>
            </x-data-table.tr>

            <x-data-table.tr>
                <td class="px-5 py-2 ">
                    <div class="text-lg text-gray-500 dark:text-gray-100 font-bold">1</div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <x-book.book-info
                        image="https://i.gr-assets.com/images/S/compressed.photo.goodreads.com/books/1554186848i/44768375._UY844_SS844_.jpg"
                        grade="Grade 11" subject="Mathematics" type="Student Text Book" edition="1st Edition 2010"
                        ISBN="4820715" />
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <x-stat.package-info batch="PO10387618" quantity="50" />
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <x-organization.info image="logom.png" name="Ministry of Education" email="mail@ministry.com"
                        phone="0987654321" />
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <x-organization.info h=12 image="{{ Auth::user()->profile_photo_url }}" name="Abebe Kebede"
                        email="abe@kebede.com" phone="0987654312" />
                </td>

                <td class="px-5 py-2 ">
                    <div class="text-gray-600 font-semibold dark:text-gray-300">1 day ago</div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <x-button btnType="warning" class="">Sent</x-button>
                </td>

                <td class="px-5 py-2">
                    <x-action.table-button id="{{ $record }}" view="viewBook" />
                </td>
            </x-data-table.tr>

            <x-data-table.tr>
                <td class="px-5 py-2 ">
                    <div class="text-lg text-gray-500 dark:text-gray-100 font-bold">1</div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <x-book.book-info
                        image="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTO4PF0UBFIlAShsDV_dJAqaXPUMuSHLAT-bcp6nyF52wSILxHhmiXOjB2ZYb8Vri76pbI&usqp=CAU"
                        grade="Grade 12" subject="Physics" type="Student Text Book" edition="2nd Edition 2014"
                        ISBN="2367832" />
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <x-stat.package-info batch="POB10387618" quantity="76" range="7235662 - 7235134" />
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <x-organization.info image="logom.png" name="Ministry of Education" email="mail@ministry.com"
                        phone="0987654321" />
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <x-organization.info h=12 image="{{ Auth::user()->profile_photo_url }}" name="Abebe Kebede"
                        email="abe@kebede.com" phone="0987654312" />
                </td>

                <td class="px-5 py-2 ">
                    <div class="text-gray-600 font-semibold dark:text-gray-300">2 weeks ago</div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <x-button btnType="info" class="">Stored</x-button>
                </td>

                <td class="px-5 py-2">
                    <x-action.table-button id="{{ $record }}" view="viewBook" />
                </td>
            </x-data-table.tr>
            {{-- @empty --}}
            {{-- <x-data-table.empty colspan=6 /> --}}
            {{-- @endforelse --}}
        </x-slot>
    </x-form.table>
</div>