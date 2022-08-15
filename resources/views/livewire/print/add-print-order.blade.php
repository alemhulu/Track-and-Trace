<div>
    <x-form.card function="addPrintOrder" title="Add New Print Order">
        <form class=" space-y-8 divide-y divide-gray-200">
            <div class="space-y-8 divide-y divide-gray-200 sm:space-y-5">
                {{-- Basic Profile information --}}
                <div>
                    <div>
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            Print Info
                        </h3>
                        <p class="mt-1 max-w-2xl text-sm text-gray-500">
                            Basic Book Print Information
                        </p>
                    </div>

                    <div class="mt-6 sm:mt-5 space-y-6 sm:space-y-5">
                        <div
                            class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
                            <x-jet-label value="Batch Number" />
                            <div class="mt-1 sm:mt-0 sm:col-span-2">
                                <span
                                    class="max-w-xl text-lg rounded-md border py-2 px-4 text-gray-600 font-semibold bg-blue-50 hover:bg-blue-100 hover:font-bold shadow-lg shadow-blue-50">BTSC12634523971</span>
                            </div>
                        </div>

                        <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start  sm:pt-3">
                            <x-jet-label value="Package QR code" />
                            <div class="mt-1 sm:mt-0 sm:col-span-2">
                                <div class="grid grid-cols-2 gap-5">
                                    <div class="col-span-1">
                                        <x-jet-label value="Start" />
                                        <span
                                            class="relative flex mt-1 text-lg rounded-md border py-2 px-4 text-gray-600 font-semibold bg-blue-50 hover:bg-blue-100 hover:font-bold shadow-lg shadow-blue-50">
                                            0000000000001
                                            {{-- <i class="absolute right-2 top-3  fi fi-rr-hastag"></i> --}}
                                        </span>
                                    </div>

                                    <div class="col-span-1">
                                        <x-jet-label value="End" />
                                        <span
                                            class="flex mt-1 text-lg rounded-md border py-2 px-4 text-gray-600 font-semibold bg-blue-50 hover:bg-blue-100 hover:font-bold shadow-lg shadow-blue-50">
                                            0000000001000
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start  sm:pt-3">
                            <x-jet-label value="Book Barcode" />
                            <div class="mt-1 sm:mt-0 sm:col-span-2">
                                <div class="grid grid-cols-2 gap-5">
                                    <div class="col-span-1">
                                        <x-jet-label value="Start" />
                                        <span
                                            class="flex mt-1 text-lg rounded-md border py-2 px-4 text-gray-600 font-semibold bg-blue-50 hover:bg-blue-100 hover:font-bold shadow-lg shadow-blue-50">
                                            11010203010001
                                        </span>
                                    </div>

                                    <div class="col-span-1">
                                        <x-jet-label value="End" />
                                        <span
                                            class="flex mt-1 text-lg rounded-md border py-2 px-4 text-gray-600 font-semibold bg-blue-50 hover:bg-blue-100 hover:font-bold shadow-lg shadow-blue-50">
                                            11010203019001
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Contact Person Information --}}
                <div class="pt-8 space-y-6 sm:pt-10 sm:space-y-5">
                    <div>
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            Print Book
                        </h3>
                        <p class="mt-1 max-w-2xl text-sm text-gray-500">
                            Select Book To Order Print
                        </p>
                    </div>
                    <div class="space-y-6 sm:space-y-5">
                        <div
                            class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
                            <x-jet-label value="Grade" />
                            <div class="mt-1 sm:mt-0 sm:col-span-2">
                                <x-form.select wire:model="user_id" id="user_id" class="max-w-md">
                                    <option>select grade</option>
                                    <option>Grade 10</option>
                                    <option>Grade 12</option>
                                </x-form.select>
                            </div>
                        </div>

                        <div
                            class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
                            <x-jet-label value="Subject" />
                            <div class="mt-1 sm:mt-0 sm:col-span-2">
                                <x-form.select wire:model="user_id" id="user_id" class="max-w-md">
                                    <option>select subject</option>
                                    <option>Biology</option>
                                    <option>English</option>
                                </x-form.select>
                            </div>
                        </div>

                        <div
                            class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
                            <x-jet-label value="Select Books" />
                            <div class="mt-1 sm:mt-0 sm:col-span-2 grid grid-cols-2 gap-5">
                                <div>
                                    <x-jet-label value="Student Text Book" />
                                    <x-form.select wire:model="user_id" id="user_id" class="max-w-md">
                                        <option>select subject</option>
                                        <option>Biology</option>
                                        <option>English</option>
                                    </x-form.select>
                                </div>

                                <div>
                                    <x-jet-label value="Teacher Guide Books" />
                                    <x-form.select wire:model="user_id" id="user_id" class="max-w-md">
                                        <option>select subject</option>
                                        <option>Biology</option>
                                        <option>English</option>
                                    </x-form.select>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <div class="pt-8 space-y-6 sm:pt-10 sm:space-y-5">
                    <div>
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            Order Print
                        </h3>
                        <p class="mt-1 max-w-2xl text-sm text-gray-500">
                            Fill The Necessary Infomation To Order The Print
                        </p>
                    </div>
                    <div class="space-y-6 sm:space-y-5">
                        <div
                            class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
                            <x-jet-label value="Organazation" />
                            <div class="mt-1 sm:mt-0 sm:col-span-2">
                                <x-form.select wire:model="country_id" id="country_id" class="max-w-md">
                                    <option>select campany</option>
                                    <option>School</option>
                                    <option>Campany</option>
                                </x-form.select>
                            </div>
                        </div>

                        <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start  sm:pt-3">
                            <x-jet-label value="Number of Copies" />
                            <div class="mt-1 sm:mt-0 sm:col-span-2">
                                <div class="grid grid-cols-2 gap-5">
                                    <div class="col-span-1">
                                        <x-jet-label value="Student Text Books" />
                                        <x-jet-input type="number" @class(['max-w-md'])
                                            placeholder="inter number of copies" />
                                    </div>

                                    <div class="col-span-1">
                                        <x-jet-label value="Teacher Guide Books" />
                                        <span
                                            class="flex mt-1 w-36 text-lg rounded-md border py-1.5 px-4 text-gray-600 font-semibold bg-blue-50 hover:bg-blue-100 hover:font-bold shadow-lg shadow-blue-50 mr-2">13
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:pt-5">
                            <x-jet-label value="Number of Packages" />
                            <div class="mt-1 sm:mt-0 sm:col-span-2">
                                <span
                                    class="max-w-xl text-lg rounded-md border py-2 px-4 text-gray-600 font-semibold bg-blue-50 hover:bg-blue-100 hover:font-bold shadow-lg shadow-blue-50 mr-2">40
                                </span> Packages
                            </div>
                        </div>

                        <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:pt-5">
                            <x-jet-label value="Schedule Delivery Date" />
                            <div class="mt-1 sm:mt-0 sm:col-span-2">
                                <x-jet-input type="date" class="max-w-sm" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </x-form.card>
</div>