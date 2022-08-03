<div>
    <x-form.card function="addBook" title="Add New Book">
        <form class=" space-y-8 divide-y divide-gray-200">
            <div class="space-y-8 divide-y divide-gray-200 sm:space-y-5">
                <div>
                    <div>
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            Book
                        </h3>
                        <p class="mt-1 max-w-2xl text-sm text-gray-500">
                            Basic Book information
                        </p>
                    </div>

                    <div class="mt-6 sm:mt-5 space-y-6 sm:space-y-5">
                        <div
                            class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
                            <x-jet-label value="Grade" />
                            <div class="mt-1 sm:mt-0 sm:col-span-2">
                                <x-form.select wire:model="grade_id" id="grade_id" class="max-w-md">
                                    <option>grade</option>
                                    <option>10</option>
                                    <option>12</option>
                                </x-form.select>
                            </div>
                        </div>

                        <div
                            class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
                            <x-jet-label value="Subject" />
                            <div class="mt-1 sm:mt-0 sm:col-span-2">
                                <x-form.select wire:model="subject_id" id="subject_id" class="max-w-md">
                                    <option>subject</option>
                                    <option>Maths</option>
                                    <option>Biology</option>
                                </x-form.select>
                            </div>
                        </div>

                        <div
                            class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
                            <x-jet-label value="Book Type" />
                            <div class="mt-1 sm:mt-0 sm:col-span-2">
                                <x-form.select wire:model="bookType_id" id="bookType_id" class="max-w-md">
                                    <option>type</option>
                                    <option>Student Text Book</option>
                                    <option>Teacher Guide</option>
                                </x-form.select>
                            </div>
                        </div>

                        <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start  sm:pt-3">
                            <x-jet-label value="Edition" />
                            <div class="mt-1 sm:mt-0 sm:col-span-2">
                                <x-jet-input type="text" name="edition" id="edition" class="max-w-xl" />
                            </div>
                        </div>

                        <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start  sm:pt-3">
                            <x-jet-label value="ISBN Number" />
                            <div class="mt-1 sm:mt-0 sm:col-span-2">
                                <x-jet-input type="text" name="isbn" id="isbn" class="max-w-xl" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="pt-8 space-y-6 sm:pt-10 sm:space-y-5">
                <div>
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        Book Standards
                    </h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">
                        Select or wirte the Book standards.
                    </p>
                </div>

                <div class="space-y-6 sm:space-y-5">
                    <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start  sm:pt-3">
                        <x-jet-label value="Print Type" />
                        <div class="mt-1 sm:mt-0 sm:col-span-2">
                            <x-form.select wire:model="printType_id" id="printType_id" class="max-w-md">
                                <option>print type</option>
                                <option>Color Print (RGB)</option>
                                <option>Black and White Print</option>
                            </x-form.select>
                        </div>
                    </div>

                    <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start  sm:pt-3">
                        <x-jet-label value="Paper Size" />
                        <div class="mt-1 sm:mt-0 sm:col-span-2">
                            <x-form.select wire:model="paperSize_id" id="paperSize_id" class="max-w-md">
                                <option>paper size</option>
                                <option>A4</option>
                                <option>A5</option>
                            </x-form.select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="pt-8 space-y-6 sm:pt-10 sm:space-y-5">
                <div>
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        Book Files
                    </h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">
                        Upload Book Files based on the book standards.
                    </p>
                </div>
                <div class="space-y-6 sm:space-y-5">
                    <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
                        <x-jet-label value="Upload Book File" />
                        <div class="mt-1 sm:mt-0 sm:col-span-2">
                            <x-jet-input type="file" name="font" id="bookUrl" class="max-w-lg text-gray-500 file:mr-4 file:py-2 file:px-4
                            file:rounded-lg file:border-0
                            file:text-sm file:font-semibold
                            file:bg-blue-50 file:text-blue-700
                            hover:file:bg-blue-100 file:focus:ring-0 file:focus:border-lime-200" />
                        </div>
                    </div>

                    <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:pt-3">
                        <x-jet-label value="Book Cover Image" />
                        <div class="mt-1 sm:mt-0 sm:col-span-2">
                            <div class="grid grid-cols-2 gap-4 items-start pt-3 ">
                                <div class="flex flex-col lg:flex-row lg:space-x-5 items-center">
                                    <img src="" alt="" srcset=""
                                        class=" h-48 w-32 border-dashed border-gray-200 rounded bg-cover">
                                    <label for="front"
                                        class="mt-4 py-1 px-4 text-center bg-blue-50 w-32 text-blue-700 rounded text-sm font-semibold">Choose
                                        Front</label>
                                    <x-jet-input type="file" name="font" id="front" class="hidden" />
                                </div>

                                <div class="flex flex-col lg:flex-row lg:space-x-5 items-center">
                                    <img src="" alt="" srcset=""
                                        class="h-48 w-32 border-dashed border-gray-200 rounded bg-cover">
                                    <label for="back"
                                        class="mt-4 py-1 px-4 text-center bg-blue-50 w-32 text-blue-700 rounded text-sm font-semibold">Choose
                                        Back</label>
                                    <x-jet-input type="file" name="font" id="back" class="hidden" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </x-form.card>
</div>