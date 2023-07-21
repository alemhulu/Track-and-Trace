@php
use Carbon\Carbon;
@endphp
<div>
    <x-form.table title="Request List">
        <x-slot name="tableHeaders">
            <x-data-table.th scope="col">#</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Name') }}</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Package') }}</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Organization') }}</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Conact Person') }}</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Received Date') }}</x-data-table.th>
            <x-data-table.th scope="col"> {{__('Status') }}</x-data-table.th>
            <x-data-table.th scope="col" class="sr-only">{{__('Action') }}</x-data-table.th>
        </x-slot>

        <x-slot name="tableRows">
            @php $i = 1; $record = 1;@endphp
            {{-- @forelse($books as $record) --}}
            {{-- <x-data-table.tr>
                <td class="px-5 py-2 ">
                    <div class="text-lg text-gray-500 dark:text-gray-100 font-bold">1</div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <x-book.book-info image="/biology-grade-10.jpg" grade="Grade 10"
                        subject="Biology" type="Student Text Book" edition="1st Edition 2013" ISBN="4820715" />
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
                <div class="text-gray-600 font-semibold dark:text-gray-300">4 hours ago</div>
            </td>

            <td class="px-5 py-2 whitespace-nowrap">
                <x-button btnType="info" class="">Stored</x-button>
            </td>

            <td class="px-5 py-2">
                <x-action.table-button id="{{ $record }}" view="viewBook" />
            </td>
            </x-data-table.tr> --}}

            @foreach ($packages as $package)
            <x-data-table.tr>
                <td class="px-5 py-2 ">
                    <div class="text-lg text-gray-500 dark:text-gray-100 font-bold">{{ $i++ }}</div>
                </td>
                {{-- @dump($package) --}}
                <td class="px-5 py-2 whitespace-nowrap">
                    <x-book.book-info image="{{ $package['0']['print_order']['book']['front_cover_location'] }}"
                        grade="Grade {{ $package['0']['print_order']['book']['grade_id'] }}"
                        subject="{{ $package['0']['subject']['name'] }}" type="Student Text Book"
                        edition="{{ $package['0']['print_order']['book']['edition'] }}st Edition"
                        ISBN="{{ $package['0']['print_order']['book']['isbn'] }}" />
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <x-stat.package-info batch="PO2349856"
                        quantity="{{ $package['0']['print_order']['no_of_packages'] }}"
                        range="{{ $package['0']['print_order']['qrcode_start'] }} - {{ $package['0']['print_order']['qrcode_end'] }}" />
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <x-organization.info image="{{ $package['0']['organization']['logo'] }}"
                        name="{{ $package['0']['organization']['name'] }}"
                        email="{{ $package['0']['organization']['email'] }}"
                        phone="{{ $package['0']['organization']['telephone'] ?? 'Phone ___' }}" />
                </td>
                {{-- @dump($package) --}}
                <td class="px-5 py-2 whitespace-nowrap">
                    <x-organization.info h=12
                        image="{{ $package['0']['organization']['assigned_user']['profile_photo_path']}}"
                        name="{{ $package['0']['organization']['assigned_user']['name']}}"
                        email="{{ $package['0']['organization']['assigned_user']['email']}}"
                        phone="{{ $package['0']['organization']['assigned_user']['phone'] ?? 'Phone ___'}}" />
                </td>

                <td class="px-5 py-2 ">
                    <div class="text-gray-600 font-semibold dark:text-gray-300">
                        {{ Carbon::parse( $package['0']['created_at'])->diffForHumans() }}
                    </div>
                </td>

                <td class="px-5 py-2 whitespace-nowrap">
                    <x-button btnType="info" class="">Stored</x-button>
                </td>

                <td class="px-5 py-2">
                    <x-action.table-button id="{{ $record }}" view="viewBook" />
                </td>
            </x-data-table.tr>
            @endforeach

            {{-- @empty --}}
            {{-- <x-data-table.empty colspan=6 /> --}}
            {{-- @endforelse --}}
        </x-slot>
    </x-form.table>
</div>