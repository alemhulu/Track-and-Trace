<div>
    <x-form.card function="addPrintOrder" title="Add New Print Order">
        <form class=" space-y-8 divide-y divide-gray-200 dark:divide-gray-600">
            <div class="space-y-8 divide-y divide-gray-200 dark:divide-gray-600 sm:space-y-5">
                {{-- Contact Person Information --}}
                <div class="pt-8 space-y-6 sm:pt-10 sm:space-y-5">
                    <div>
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-50">
                            Print Book
                        </h3>
                        <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                            Select Book To Order Print
                        </p>
                    </div>
                    <div class="space-y-6 sm:space-y-5">
                        <div
                            class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
                            <x-jet-label value="Grade" />
                            <div class="mt-1 sm:mt-0 sm:col-span-2">
                                <x-form.select wire:model="grade_id" id="user_id" class="max-w-md">
                                    <option>Select Grade</option>
                                    @foreach ($grades as $grade)
                                    <option value="{{ $grade->id }}">{{ $grade->name }}</option>
                                    @endforeach
                                </x-form.select>
                            </div>
                        </div>

                        <div
                            class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
                            <x-jet-label value="Subject" />
                            <div class="mt-1 sm:mt-0 sm:col-span-2">
                                <x-form.select wire:model="subject_id" id="user_id" class="max-w-md">
                                    <option>Select Subject</option>
                                    @foreach ($subjects as $subject)
                                    <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                                    @endforeach
                                </x-form.select>
                            </div>
                        </div>

                        <div
                            class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
                            <x-jet-label value="Select Books" />
                            <div class="mt-1 sm:mt-0 col-span-2 sm:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-5 ">
                                <div>
                                    <x-jet-label value="Student Text Book" />
                                    <x-form.select wire:model="book_id" id="user_id" class="mt-0 max-w-md">
                                        <option>Select Books</option>
                                        @foreach ($books as $book)
                                        <option value="{{ $book->id }}">
                                            {{ $book->subject->name }}
                                        </option>
                                        @endforeach
                                    </x-form.select>
                                </div>

                                <div>
                                    <x-jet-label value="Teacher Guide Books" />
                                    <x-form.select wire:model="book_id" id="user_id" class="mt-0 max-w-md">
                                        <option>Select Books</option>
                                        @foreach ($books as $book)
                                        <option value="{{ $book->id }}">
                                            {{ $book->subject->name }}
                                        </option>
                                        @endforeach
                                    </x-form.select>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <div class="pt-8 space-y-6 sm:pt-10 sm:space-y-5">
                    <div>
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-50">
                            Order Print
                        </h3>
                        <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                            Fill The Necessary Infomation To Order The Print
                        </p>
                    </div>
                    <div class="space-y-6 sm:space-y-5">
                        <div
                            class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
                            <x-jet-label value="Organazation Type" />
                            <div class="mt-1 sm:mt-0 sm:col-span-2">
                                <x-form.select wire:model="organization_type_id" id="organization_type_id"
                                    class="max-w-md">
                                    <option>Select Organization Type</option>
                                    @foreach ($organizationTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                    @endforeach


                                </x-form.select>
                            </div>
                        </div>
                        <div
                            class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
                            <x-jet-label value="Organazation" />
                            <div class="mt-1 sm:mt-0 sm:col-span-2">
                                <x-form.select wire:model="organization_id" id="organization_id" class="max-w-md">
                                    <option selected>Select Print Order Organization</option>
                                    @foreach ($organizations as $organization)
                                    <option value="{{ $organization->id }}">{{ $organization->name }}</option>
                                    @endforeach


                                </x-form.select>
                            </div>
                        </div>

                        <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start  sm:pt-3">
                            <x-jet-label value="Number of Copies" />
                            <div class="mt-1 sm:mt-0 sm:col-span-2">
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                                    <div>
                                        <x-jet-label value="Student Text Books" />
                                        <x-jet-input wire:model="number_of_copies" type="number" @class(['max-w-md'])
                                            max="10000000" placeholder="inter number of copies" />
                                    </div>
                                    <div>
                                        <x-jet-label value="Number of Packages" />
                                        <x-jet-input wire:model="number_of_packages" type="number" @class(['max-w-md'])
                                            placeholder="Teacher guide books" max="10000000" />
                                    </div>
                                    <div>
                                        <x-jet-label value="Books per Package" />
                                        <x-jet-input disabled type="number" @class(['max-w-md'])
                                            placeholder="Teacher guide books" max="10000000"
                                            value="{{ $bookPerPackage ?? 0}}" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:pt-5">
                            <x-jet-label value="Teacher's Gide Books" />
                            <div class="mt-1 sm:mt-0 sm:col-span-2 dark:text-gray-300">
                                <span
                                    class="max-w-xl text-lg rounded-md border py-2 px-4 text-gray-600 font-semibold bg-blue-50 hover:bg-blue-100 hover:font-bold shadow-lg shadow-blue-50 dark:shadow-gray-700 mr-2">{{ $bookPerPackage ??'0'}}
                                </span> Packages
                            </div>
                        </div>

                        <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:pt-5">
                            <x-jet-label value="Schedule Delivery Date" />
                            <div class="mt-1 sm:mt-0 sm:col-span-2">
                                <x-jet-input wire:model.defer="expected_print_date" type="date" class="max-w-sm" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            {{-- Basic Profile information --}}
            <div>
                <div>
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-50">
                        Print Info
                    </h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                        Basic Book Print Information
                    </p>
                </div>

                <div class="mt-6 sm:mt-5 space-y-6 sm:space-y-5">
                    <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
                        <x-jet-label value="Batch Number" />
                        <div class="mt-1 sm:mt-0 sm:col-span-2">
                            <span
                                class="max-w-xl text-lg rounded-md border py-2 px-4 text-gray-600 font-semibold bg-blue-50 hover:bg-blue-100 hover:font-bold shadow-lg shadow-blue-50 dark:shadow-gray-700">{{
                                $barcode_start }}</span>
                        </div>
                    </div>

                    <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start  sm:pt-3">
                        <x-jet-label value="Package QR code" />
                        <div class="mt-1 sm:mt-0 sm:col-span-2">
                            <div class="grid sm:grid-cols-2 gap-5">
                                <div class="col-span-1">
                                    <x-jet-label value="Start" />
                                    <span
                                        class=" flex sm:mt-1 text-lg rounded-md border py-2 px-4 text-gray-500 font-semibold bg-blue-50 hover:bg-blue-100 hover:font-bold shadow-lg shadow-blue-50 dark:shadow-gray-700 h-10">
                                        {{ $qrcode_start }}
                                        {{-- <i class="absolute right-2 top-3  fi fi-rr-hastag"></i> --}}
                                    </span>
                                </div>

                                <div class="col-span-1">
                                    <x-jet-label value="End" />
                                    <span
                                        class="flex sm:mt-1 text-lg rounded-md border py-2 px-4 text-gray-500 font-semibold bg-blue-50 hover:bg-blue-100 hover:font-bold shadow-lg shadow-blue-50 dark:shadow-gray-700 h-10">
                                        {{ $qrcode_end}}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <img class="w-22" src="{{ $path2 }}" alt="abebe">
                    <img class="w-15" src="{{ $path }}" alt="test">
                    <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start  sm:pt-3">
                        <x-jet-label value="Book Barcode" />
                        <div class="mt-1 sm:mt-0 sm:col-span-2">
                            <div class="grid sm:grid-cols-2 gap-5">
                                <div class="col-span-1">
                                    <x-jet-label value="Start" />
                                    <span
                                        class="flex sm:mt-1 text-lg rounded-md border py-2 px-4 text-gray-500 font-semibold bg-blue-50 hover:bg-blue-100 hover:font-bold shadow-lg shadow-blue-50 dark:shadow-gray-700 h-10">
                                        {{ $barcode_start}}
                                    </span>
                                </div>

                                <div class="col-span-1">
                                    <x-jet-label value="End" />
                                    <span
                                        class="flex sm:mt-1 text-lg rounded-md border py-2 px-4 text-gray-500 font-semibold bg-blue-50 hover:bg-blue-100 hover:font-bold shadow-lg shadow-blue-50 dark:shadow-gray-700 h-10">
                                        {{ $barcode_end}}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </x-form.card>
</div>