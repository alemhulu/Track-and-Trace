<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:text-gray-200">
            {{ __('Print Order Detail') }}
        </h2>
    </x-slot>
    <div>
        <x-form.card title="Packages Request Info" buttons="">

            <div class="flex flex-auto lg:justify-between flex-wrap space-x-2 gap-x-5 items-center">
                <div class="">
                    <x-jet-label class="text-gray-400" value="Book"></x-jet-label>
                    <x-book.book-info image="{{ $order->book->front_cover_location }}"
                        grade="Grade {{ $order->book->grade->name }}" subject="{{ $order->book->subject->name }}"
                        type="{{ $order->book->book_type ?  'Teacher Guide' : 'Student Text Book'}}"
                        edition="{{ $order->book->edition }}st Edition {{ $order->book->created_at->format('Y') }}"
                        ISBN="{{ $order->book->isbn }}" />
                </div>
                <div class="flex-grow max-w-xs flex-initial">
                    <x-jet-label class="text-gray-400" value="Packages Info"></x-jet-label>
                    <x-stat.package-info batch="PB{{ $order->created_at->format('Y') }}{{ $order->id }}"
                        quantity="{{ $order->no_of_packages }}" range="" />
                </div>

                <div class="">
                    <x-jet-label class="text-gray-400" value="Organization"></x-jet-label>
                    <x-organization.info image="{{ $order->orderOrganization->logo }}"
                        name="{{ $order->orderOrganization->name }}" email="{{ $order->orderOrganization->email }}"
                        phone="+{{ $order->orderOrganization->telephone }}" />
                </div>

                <div class="">
                    <x-jet-label class="text-gray-400" value="Contact Person"></x-jet-label>
                    <x-organization.info image="{{ $order->orderOrganization->logo }}"
                        name="{{ $order->orderOrganization->assignedUser->name }}"
                        email="{{ $order->orderOrganization->assignedUser->email }}"
                        phone="{{ $order->orderOrganization->assignedUser->phone }}" />
                </div>

                <div class="">
                    <x-jet-label class="text-gray-400" value="Status"></x-jet-label>
                    <x-button
                        btnType="{{ $order->request_status == 0 ? 'warning' : ($order->request_status == 1 ? 'primary' : ( $order->request_status == 2 ?  'success' : ($order->request_status == 3 ?  'info' : 'danger')) )}}">
                        {{ $order->request_status == 0 ? 'Requested' : ($order->request_status == 1 ? 'Accepted' : ( $order->request_status == 2 ?  'Printed' : ($order->request_status == 3 ?  'Sent' : 'Rejected')) )}}
                    </x-button>
                </div>
            </div>

            <div class="flex justify-between flex-wrap ml-2">
                <div class="sm:pb-4">
                    <x-jet-label class="text-gray-400" value="Request Date"></x-jet-label>
                    <div class="flex items-center gap-2 text-gray-600 dark:text-gray-300">
                        <i class="flex text-2xl fi fi-rr-time-check"></i>
                        <span class="text-md font-semibold "> {{ $order->created_at->format('M d, Y') }}</span>
                    </div>
                </div>

                <div class="space-x-2 mt-8 sm:mt-0 flex">
                    <a href="{{ route('print-order.list') }}">
                        <x-button type="button" btnType="secondary">CANCEL</x-button>
                    </a>

                    @if ($order->request_status !=2 && $order->request_status != 3)
                    @if ($order->request_status !=4)
                    <a href="#confirmModal">
                        <x-button type="button" btnType="danger" wire:click="status(4)">REJECT</x-button>
                    </a>
                    @if ($order->request_status ==1)
                    <a href="#receivingForm">
                        <x-button type="button" btnType="success" wire:click="status(2)">Printed</x-button>
                    </a>
                    @endif

                    @endif

                    @if ($order->request_status !=1)
                    <a href="#receivingForm">
                        <x-button type="button" btnType="success" wire:click="status(1)">ACCEPT</x-button>
                    </a>
                    @endif
                    @endif
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
                    {{-- <x-data-table.th scope="col"> {{__('Status') }}</x-data-table.th>
                    <x-data-table.th scope="col"> {{__('Print') }}</x-data-table.th> --}}
                    <x-data-table.th scope="col" class="sr-only">{{__('Action') }}</x-data-table.th>
                </x-slot>

                <x-slot name="tableRows">
                    @forelse($order->Book_codes as $key=>$record)
                    <x-data-table.tr>
                        <td class="px-5 py-2 ">
                            <div class="text-lg text-gray-500 dark:text-gray-100 font-bold">{{ $key }}</div>
                        </td>

                        <td class="px-5 py-2 whitespace-nowrap">
                            <x-stat.package batch="PB{{ $order->created_at->format('Y') }}{{ $order->id }}" qr=""
                                Qrcode="/storage/printOrders/{{ $order->id }}/{{ $key }}/{{ $order->Book_codes[$key]['QR'] }}" />
                        </td>

                        <td class="px-5 py-2 whitespace-nowrap">
                            <x-stat.book-info grade="Grade {{ $order->book->grade->name }}" quantity="41"
                                range="
                                {{ intval(Str::substr($order->Book_codes[$key]['barcodes'][0], 0, -4))
                                }}- {{ intval(Str::substr($order->Book_codes[$key]['barcodes'][$order->no_of_books/$order->no_of_packages-1], 0, -4)) }}" />
                        </td>

                        {{-- <td class="px-5 py-2 whitespace-nowrap">
                            <x-button btnType="secondary" class="">Unchecked</x-button>
                        </td>

                        <td class="px-5 py-2 whitespace-nowrap">
                            <x-form.toggle />
                        </td> --}}

                        <td class="px-5 py-2">
                            <x-action.table-button id="{{ $order->id}}" view="#Booksbarcode{{ $key }}" link />
                            <x-data-table.modal name="Booksbarcode{{ $key }}" maxWidth="7xl" :buttons="false">
                                <x-slot name="title">
                                    Books In Package {{ intval(Str::substr($order->Book_codes[$key]['QR'], 0, -4)) }}
                                </x-slot>
                                <x-slot name="body">
                                    <x-stat.package batch="PB{{ $order->created_at->format('Y') }}{{ $order->id }}"
                                        qr="{{ intval(Str::substr($order->Book_codes[$key]['QR'], 0, -4)) }}"
                                        Qrcode="/storage/printOrders/{{ $order->id }}/{{ $key }}/{{ $order->Book_codes[$key]['QR'] }}"
                                        grade="{{ $order->book->grade->name }}"
                                        subject="{{ $order->book->subject->name }}" isbn="{{ $order->book->isbn }}"
                                        volume="{{ $order->book->volume ?? '----' }}"
                                        edition="{{ $order->book->edition }}"
                                        booktype="{{ $order->book->book_type ?  'Teacher Guide' : 'Student Text Book'}}" />
                                    <div class="grid grid-cols-3 p-5 border">
                                        @foreach ($order->Book_codes[$key]['barcodes'] as $barcode)
                                        <div
                                            class="flex items-center justify-center border p-4 border-dashed border-gray-500">
                                            <img src="/storage/printOrders/{{ $order->id }}/{{ $key }}/barcods/{{ $barcode }}"
                                                alt="" srcset="">
                                        </div>
                                        @endforeach
                                    </div>

                                </x-slot>
                            </x-data-table.modal>

                        </td>
                    </x-data-table.tr>
                    @empty
                    <x-data-table.empty colspan=6 />
                    @endforelse
                </x-slot>
            </x-form.table>
        </div>


    </div>
</div>