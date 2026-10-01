<div>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
            {{ __('Print Order Detail') }}
        </h2>
    </x-slot>
    <div>
        <x-form.card title="Packages Request Info" buttons="">

            <div class="flex flex-wrap items-center flex-auto space-x-2 lg:justify-between gap-x-5">
                <div class="">
                    <x-jet-label class="text-gray-400" value="Book"></x-jet-label>
                    <x-book.book-info image="{{ $order->book->front_cover_location }}"
                        grade="Grade {{ $order->book->grade->name }}" subject="{{ $order->book->subject->name }}"
                        type="{{ $order->book->book_type ?  'Teacher Guide' : 'Student Text Book'}}"
                        edition="{{ $order->book->edition }}st Edition {{ $order->book->created_at->format('Y') }}"
                        ISBN="{{ $order->book->isbn }}" />
                </div>
                <div class="flex-initial flex-grow max-w-xs">
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

            <div class="flex flex-wrap justify-between ml-2">
                <div class="sm:pb-4">
                    <x-jet-label class="text-gray-400" value="Request Date"></x-jet-label>
                    <div class="flex items-center gap-2 text-gray-600 dark:text-gray-300">
                        <i class="flex text-2xl fi fi-rr-time-check"></i>
                        <span class="font-semibold text-md "> {{ $order->created_at->format('M d, Y') }}</span>
                    </div>
                </div>

                <div class="flex mt-8 space-x-2 sm:mt-0">
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
                    @forelse($packages as $packageKey => $record)
                    @php
                    $package = is_array($record) ? $record : [];
                    $qrFile = data_get($package, 'QR') ?? data_get($package, 'qr') ?? data_get($package, 'barcodes.0');
                    $barcodes = collect(data_get($package, 'barcodes', []))->filter()->values();
                    $booksPerPackage = max((int) ($order->no_of_packages ? $order->no_of_books / $order->no_of_packages
                    : 0), 1);
                    $endIndex = min($booksPerPackage - 1, max($barcodes->count() - 1, 0));
                    $firstBarcode = $barcodes->get(0);
                    $lastBarcode = $barcodes->get($endIndex);
                    $qrNumber = $qrFile ? intval(Str::substr($qrFile, 0, -4)) : null;
                    $rangeStart = $firstBarcode ? intval(Str::substr($firstBarcode, 0, -4)) : 'N/A';
                    $rangeEnd = $lastBarcode ? intval(Str::substr($lastBarcode, 0, -4)) : 'N/A';
                    @endphp
                    <x-data-table.tr>
                        <td class="px-5 py-2 ">
                            <div class="text-lg font-bold text-gray-500 dark:text-gray-100">
                                {{ $packages->firstItem() + $loop->index }}
                            </div>
                        </td>

                        <td class="px-5 py-2 whitespace-nowrap">
                            <x-stat.package batch="PB{{ $order->created_at->format('Y') }}{{ $order->id }}" qr=""
                                Qrcode="{{ $qrFile ? '/storage/printOrders/'.$order->id.'/'.$packageKey.'/'.$qrFile : '' }}" />
                        </td>

                        <td class="px-5 py-2 whitespace-nowrap">
                            <x-stat.book-info grade="Grade {{ $order->book->grade->name }}" quantity="41"
                                range="{{ $rangeStart }}-{{ $rangeEnd }}" />
                        </td>

                        {{-- <td class="px-5 py-2 whitespace-nowrap">
                            <x-button btnType="secondary" class="">Unchecked</x-button>
                        </td>

                        <td class="px-5 py-2 whitespace-nowrap">
                            <x-form.toggle />
                        </td> --}}

                        <td class="px-5 py-2">
                            <x-action.table-button id="{{ $order->id}}" view="#Booksbarcode{{ $packageKey }}" link />
                            <x-data-table.modal name="Booksbarcode{{ $packageKey }}" maxWidth="7xl" :buttons="false">
                                <x-slot name="title">
                                    Books In Package {{ $qrNumber ?? 'N/A' }}
                                </x-slot>
                                <x-slot name="body">
                                    <x-stat.package batch="PB{{ $order->created_at->format('Y') }}{{ $order->id }}"
                                        qr="{{ $qrNumber ?? '' }}"
                                        Qrcode="{{ $qrFile ? '/storage/printOrders/'.$order->id.'/'.$packageKey.'/'.$qrFile : '' }}"
                                        grade="{{ $order->book->grade->name }}"
                                        subject="{{ $order->book->subject->name }}"
                                        isbn="{{ $order->book->isbn }}"
                                        volume="{{ $order->book->volume ?? '----' }}"
                                        edition="{{ $order->book->edition }}"
                                        booktype="{{ $order->book->book_type ? 'Teacher Guide' : 'Student Text Book' }}" />

                                    <div class="grid grid-cols-1 gap-2 p-5 overflow-y-scroll max-h-[60vh] border md:grid-cols-2 xl:grid-cols-3">
                                        @foreach ($barcodes as $barcode)
                                        <div
                                            class="flex items-center justify-center p-4 border border-gray-500 border-dashed">
                                            <img src="/storage/printOrders/{{ $order->id }}/{{ $packageKey }}/barcods/{{ $barcode }}"
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

                <div class="px-4 py-3">
                    {{ $packages->links() }}
                </div>
            </x-form.table>
        </div>
    </div>
</div>
