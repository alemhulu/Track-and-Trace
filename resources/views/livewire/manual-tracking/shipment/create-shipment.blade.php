<div>
    <form wire:submit.prevent="dispatchShipment"
        class="p-5 bg-white rounded-lg shadow-sm dark:bg-gray-800">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">New Shipment</h3>
        <p class="mb-5 text-sm text-gray-500 dark:text-gray-400">
            Dispatching removes the books from the source's stock. They are added to the destination when it confirms receipt.
        </p>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div>
                <x-jet-label for="from_organization_id" value="From (source)" />
                <x-form.select wire:model="from_organization_id" id="from_organization_id">
                    <option value="">Select source</option>
                    @foreach ($sources as $organization)
                    <option value="{{ $organization->id }}">{{ $organization->name }}</option>
                    @endforeach
                </x-form.select>
            </div>
            <div>
                <x-jet-label for="destinationRegionFilter" value="Destination region" />
                <x-form.select wire:model="destinationRegionFilter" id="destinationRegionFilter">
                    <option value="">All regions</option>
                    @foreach ($regions as $region)
                    <option value="{{ $region->id }}">{{ $region->name }}</option>
                    @endforeach
                </x-form.select>
            </div>
            <div>
                <x-jet-label for="to_organization_id" value="To (destination)" />
                <x-form.select wire:model="to_organization_id" id="to_organization_id">
                    <option value="">Select destination</option>
                    @foreach ($destinations as $organization)
                    <option value="{{ $organization->id }}">{{ $organization->name }}</option>
                    @endforeach
                </x-form.select>
                @if ($zoneFilter)
                <p class="mt-1 text-xs text-gray-500">
                    Showing {{ $zoneFilter->name }} only ·
                    <button type="button" wire:click="clearZoneFilter" class="text-blue-700 hover:underline dark:text-blue-300">show all</button>
                </p>
                @endif
            </div>

            <div>
                <x-jet-label for="carrier" value="Carrier" />
                <x-jet-input wire:model.defer="carrier" id="carrier" type="text" placeholder="e.g. Ethiopian Airlines Cargo" class="block w-full mt-1" />
            </div>
            <div>
                <x-jet-label for="waybill_no" value="Waybill number" />
                <x-jet-input wire:model.defer="waybill_no" id="waybill_no" type="text" class="block w-full mt-1" />
            </div>
            <div>
                <x-jet-label for="expected_at" value="Expected arrival" />
                <x-jet-input wire:model.defer="expected_at" id="expected_at" type="date" class="block w-full mt-1" />
                <x-jet-input-error for="expected_at" class="mt-2" />
            </div>
            <div>
                <x-jet-label for="vehicle_no" value="Vehicle plate" />
                <x-jet-input wire:model.defer="vehicle_no" id="vehicle_no" type="text" class="block w-full mt-1" />
            </div>
            <div>
                <x-jet-label for="driver_name" value="Driver" />
                <x-jet-input wire:model.defer="driver_name" id="driver_name" type="text" class="block w-full mt-1" />
            </div>
            <div>
                <x-jet-label for="driver_phone" value="Driver phone" />
                <x-jet-input wire:model.defer="driver_phone" id="driver_phone" type="text" class="block w-full mt-1" />
            </div>
        </div>

        <div class="mt-6">
            <div class="flex items-center justify-between mb-2">
                <div class="flex flex-wrap items-center gap-3">
                    <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-100">Titles</h4>
                    <x-shipment.entry-mode model="entryMode" :value="$entryMode" />
                </div>
                <button type="button" wire:click="addLine" class="text-sm font-medium text-blue-700 hover:underline dark:text-blue-300">+ Add title</button>
            </div>
            <x-jet-input-error for="lines" class="mb-2" />

            <x-scan.scanner class="mb-3" placeholder="Scan a book to pick its title, then scan each full package" />
            @if ($scanWarnings)
            <ul class="p-3 mb-3 space-y-1 text-sm text-amber-800 bg-amber-50 rounded-md dark:bg-amber-900/30 dark:text-amber-200">
                @foreach ($scanWarnings as $warning)
                <li>{{ $warning }}</li>
                @endforeach
            </ul>
            @endif

            <div class="overflow-x-auto border border-gray-200 rounded-lg dark:border-gray-700">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-gray-600 bg-gray-50 dark:bg-gray-700 dark:text-gray-300">
                        <tr>
                            <th class="px-3 py-2">Title</th>
                            @if ($entryMode === 'totals')
                            <th class="px-3 py-2">Total student books</th>
                            <th class="px-3 py-2">Total teacher guides</th>
                            <th class="px-3 py-2">Usual packing</th>
                            @else
                            <th class="px-3 py-2">Full packages</th>
                            <th class="px-3 py-2">Partial package: student books</th>
                            <th class="px-3 py-2">Partial package: teacher guides</th>
                            @endif
                            <th class="px-3 py-2">Copies</th>
                            <th class="px-3 py-2">Available at source</th>
                            <th class="px-3 py-2"><span class="sr-only">Remove</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($lines as $index => $line)
                        @php
                            $book = $books[$line['manual_book_id']] ?? null;
                            $stock = $available[$line['manual_book_id']] ?? ['student' => 0, 'teacher' => 0];
                            $copies = $lineCopies[$index];
                            $short = $copies['student'] > $stock['student'] || $copies['teacher'] > $stock['teacher'];
                        @endphp
                        <tr wire:key="line-{{ $index }}" class="{{ $activeLine === $index ? 'bg-blue-50 dark:bg-blue-900/20' : '' }}">
                            <td class="px-3 py-2 min-w-[16rem]">
                                @if (count($lines) > 1)
                                <button type="button" wire:click="setActiveLine({{ $index }})"
                                    class="mb-1 text-xs {{ $activeLine === $index ? 'font-semibold text-blue-700 dark:text-blue-300' : 'text-gray-500 hover:underline' }}">
                                    {{ $activeLine === $index ? 'Scans go here' : 'Scan into this line' }}
                                </button>
                                @endif
                                <x-form.select wire:model="lines.{{ $index }}.manual_book_id">
                                    <option value="">Select title</option>
                                    @foreach ($books as $option)
                                    <option value="{{ $option->id }}">{{ $option->title }}</option>
                                    @endforeach
                                </x-form.select>
                            </td>
@if ($entryMode === 'totals')
                            <td class="px-3 py-2">
                                <x-jet-input wire:model="lines.{{ $index }}.student_books" type="number" min="0" class="w-28" />
                            </td>
                            <td class="px-3 py-2">
                                <x-jet-input wire:model="lines.{{ $index }}.teacher_guides" type="number" min="0" class="w-24"
                                    :disabled="$book && ! $book->has_teacher_guide" />
                            </td>
                            <td class="px-3 py-2 text-xs text-gray-500 whitespace-nowrap">
                                @if ($book)
                                @php $packing = $book->packagesFor($copies['student'], $copies['teacher']); @endphp
                                {{ $packing['full_packages'] }} full{{ $packing['partial_package_books'] ? ' + 1 partial (' . $packing['partial_package_books'] . ')' : '' }}
                                @else
                                —
                                @endif
                            </td>
                            @else
                                                        <td class="px-3 py-2">
                                <x-jet-input wire:model="lines.{{ $index }}.full_packages" type="number" min="0" class="w-24" />
                            </td>
                            <td class="px-3 py-2">
                                <x-jet-input wire:model="lines.{{ $index }}.partial_package_books" type="number" min="0"
                                    max="{{ ($book->students_per_package ?? 40) - 1 }}" class="w-24" />
                            </td>
                            <td class="px-3 py-2">
                                <x-jet-input wire:model="lines.{{ $index }}.partial_teacher_guides" type="number" min="0" class="w-24"
                                    :disabled="$book && ! $book->has_teacher_guide" />
                            </td>
                            @endif
                            <td class="px-3 py-2 whitespace-nowrap text-gray-700 dark:text-gray-200">
                                {{ number_format($copies['student']) }} SB<br>{{ number_format($copies['teacher']) }} TG
                            </td>
                            <td class="px-3 py-2 whitespace-nowrap {{ $short && $book ? 'text-red-600 font-semibold' : 'text-gray-500' }}">
                                @if ($book)
                                {{ number_format($stock['student']) }} SB<br>{{ number_format($stock['teacher']) }} TG
                                @else
                                —
                                @endif
                            </td>
                            <td class="px-3 py-2 text-right">
                                @if (count($lines) > 1)
                                <button type="button" wire:click="removeLine({{ $index }})" class="text-red-600 hover:underline">Remove</button>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">SB = student books, TG = teacher guides. Printers are an external source, so no stock is checked for them.</p>
        </div>

        <div class="mt-4">
            <x-jet-label for="remarks" value="Remarks" />
            <textarea wire:model.defer="remarks" id="remarks" rows="2"
                class="block w-full mt-1 border-gray-300 rounded-md dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100"></textarea>
        </div>

        <div class="flex justify-end gap-3 mt-5">
            <a href="{{ route('manual-tracking.distribution.list') }}"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200">Cancel</a>
            <button type="submit" wire:loading.attr="disabled"
                class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700 disabled:opacity-50">Dispatch Shipment</button>
        </div>
    </form>
</div>
