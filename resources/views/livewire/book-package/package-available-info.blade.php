@php
use Carbon\Carbon;
@endphp
<div>

    <x-form.card title="Packages Available Info" buttons="">

        <div class="flex flex-auto lg:justify-between flex-wrap space-x-2 gap-x-5 items-center">
            <div class="">
                {{-- @dd($package) --}}
                <x-jet-label class="text-gray-400" value="Book"></x-jet-label>
                <x-book.book-info image="{{ $package['print_order']['book']['front_cover_location'] }}"
                    grade="Grade {{ $package['grade_id'] }}" subject="{{ $package['subject']['name'] }}"
                    type="Student Text Book" edition="{{ $package['print_order']['book']['edition'] }}st Edition"
                    ISBN="{{ $package['print_order']['book']['isbn'] }}" />
            </div>

            <div class="flex-grow max-w-xs flex-initial">
                <x-jet-label class="text-gray-400" value="Available Packages Info"></x-jet-label>
                <x-stat.package-info batch="PO10387618" quantity="{{ $package['balance'] }}" />
            </div>

            <div class="">
                <x-jet-label class="text-gray-400" value="Organization"></x-jet-label>
                <x-organization.info image="{{$package['organization']['logo']}}"
                    name=" {{$package['organization']['name']}}" email="{{$package['organization']['email']}}"
                    phone="{{$package['organization']['telephone']}}" />
            </div>

            <div class="">
                <x-jet-label class="text-gray-400" value="Contact Person"></x-jet-label>
                <x-organization.info h=12 image="{{ Auth::user()->profile_photo_url }}"
                    name="{{$package['organization']['assigned_user']['name']}}"
                    email="{{$package['organization']['assigned_user']['email']}}"
                    phone="{{$package['organization']['assigned_user']['phone'] ?? 'Phone ___'}}" />
            </div>

            <div class="">
                <x-jet-label class="text-gray-400" value="Status"></x-jet-label>
                <x-button btnType="warning" class="animate-pulse hover:animate-none">Pending</x-button>
            </div>
        </div>

        <div class="flex justify-between flex-wrap ml-2">
            <div class="sm:pb-4">
                <x-jet-label class="text-gray-400" value="Stored Date"></x-jet-label>
                <div class="flex items-center gap-2 text-gray-600 dark:text-gray-300">
                    <i class="flex text-2xl fi fi-rr-time-check"></i>
                    <span class="text-md font-semibold ">
                        {{ Carbon::parse( $package['created_at'])->diffForHumans() }}</span>
                </div>
            </div>

            <div class="space-x-2 mt-8 sm:mt-0 flex">
                <a href="{{ route('packages.request') }}">
                    <x-button type="button" btnType="secondary">CANCEL</x-button>
                </a>
                <a href="#confirmModal">
                    <x-button type="button" btnType="danger">REJECT</x-button>
                </a>
                <a href="#receivingForm">
                    <x-button type="button" btnType="success">ACCEPT</x-button>
                </a>
            </div>
        </div>
    </x-form.card>

    {{-- Table --}}
    <div>
        <x-form.table title="Packages List" search="">
            <x-slot name="tableHeaders">
                <x-data-table.th scope="col">#</x-data-table.th>
                <x-data-table.th scope="col"> {{__('Package') }}</x-data-table.th>
                <x-data-table.th scope="col"> {{__('Books') }}</x-data-table.th>
                <x-data-table.th scope="col"> {{__('Check All') }}</x-data-table.th>
                <x-data-table.th scope="col">
                    <x-button type="button" btnType="success">Send</x-button>
                </x-data-table.th>
            </x-slot>
            <x-slot name="tableRows">
                @php $i = 1; $record = count($package['Book_codes']);
                $start =5;
                @endphp
                {{-- @forelse($books as $record) --}}
                @foreach ($package['Book_codes'] as $index=>$code)
                <x-data-table.tr>
                    <td class="px-5 py-2 ">
                        <div class="text-lg text-gray-500 dark:text-gray-100 font-bold">{{ $i++ }}</div>
                    </td>

                    <td class="px-5 py-2 whitespace-nowrap">
                        <x-stat.package batch="POB10387618" qr="{{ intval(Str::substr($code['QR'] , 0, -4)) }}" />
                    </td>

                    <td class="px-5 py-2 whitespace-nowrap">
                        <x-stat.book-info grade="Grade {{ $package['grade_id'] }}" quantity=""
                            range="{{ intval(Str::substr($code['barcodes'][0] , 0, -4)) }} - {{ intval(Str::substr($code['barcodes'][0] , 0, -4)) + ($package['no_of_books']/$package['books_per_package']-1)}}" />
                    </td>


                    <td class="px-5 py-2 whitespace-nowrap">
                        <x-form.toggle />
                    </td>
                </x-data-table.tr>
                @endforeach

                {{-- @empty --}}
                {{-- <x-data-table.empty colspan=6 /> --}}
                {{-- @endforelse --}}
            </x-slot>
        </x-form.table>
    </div>

    {{-- Modals --}}
    <livewire:book-package.packages-receiving-from />
    <livewire:book-package.packages-rejecting-from />
    <x-form.confirm name="confirmModal" type="frist" message="reject" second="reasonForm" />
</div>