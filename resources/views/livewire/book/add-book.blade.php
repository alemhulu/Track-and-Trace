<div>
    <x-form.card function="addBook" title="Add New Book">
        <form class=" space-y-8 divide-y divide-gray-200">
            <div class="space-y-8 divide-y divide-gray-200 sm:space-y-5">
                <div>
                    <div>
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-50">
                            Book
                        </h3>
                        <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                            Basic Book information
                        </p>
                    </div>

                    <div class="mt-6 sm:mt-5 space-y-6 sm:space-y-5">
                        <div
                            class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
                            <x-jet-label value="Grade" />
                            <div class="mt-1 sm:mt-0 sm:col-span-2">
                                <x-form.select wire:model="grade_id" id="grade_id" class="max-w-md">
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
                                <x-form.select wire:model="subject_id" id="subject_id" class="max-w-md">
                                    <option>Select Subject</option>
                                    @foreach ($subjects as $subject)
                                    <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                                    @endforeach

                                </x-form.select>
                            </div>
                        </div>

                        <div
                            class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
                            <x-jet-label value="Book Type" />
                            <div class="mt-1 sm:mt-0 sm:col-span-2">
                                <x-form.select wire:model="book_type" id="bookType_id" class="max-w-md">
                                    <option>Select Book type</option>
                                    <option value="0">Student Text Book</option>
                                    <option value="1">Teacher Guide</option>
                                </x-form.select>
                            </div>
                        </div>

                        <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start  sm:pt-3">
                            <x-jet-label value="Edition" />
                            <div class="mt-1 sm:mt-0 sm:col-span-2">
                                <x-jet-input wire:model.defer="edition" type="number" name="edition" id="edition"
                                    class="max-w-xl" />
                            </div>
                        </div>
                        <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start  sm:pt-3">
                            <x-jet-label value="Volume" />
                            <div class="mt-1 sm:mt-0 sm:col-span-2">
                                <x-jet-input wire:model.defer="volume" type="number" name="volume" id="volume"
                                    class="max-w-xl" />
                            </div>
                        </div>
                        <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start  sm:pt-3">
                            <x-jet-label value="ISBN Number" />
                            <div class="mt-1 sm:mt-0 sm:col-span-2">
                                <x-jet-input wire:model.defer="isbn" type="text" name="isbn" id="isbn"
                                    class="max-w-xl" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="pt-8 space-y-6 sm:pt-10 sm:space-y-5">
                <div>
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-50">
                        Book Standards
                    </h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                        Select or wirte the Book standards.
                    </p>
                </div>

                <div class="space-y-6 sm:space-y-5">
                    <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start  sm:pt-3">
                        <x-jet-label value="Print Type" />
                        <div class="mt-1 sm:mt-0 sm:col-span-2">
                            <x-form.select wire:model.defer="print_type" id="printType_id" class="max-w-md">
                                <option>print type</option>
                                <option value="1">Color Print (RGB)</option>
                                <option value="0">Black and White Print</option>
                            </x-form.select>
                        </div>
                    </div>

                    <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start  sm:pt-3">
                        <x-jet-label value="Paper Size" />
                        <div class="mt-1 sm:mt-0 sm:col-span-2">
                            <x-form.select wire:model.defer="paper_size" id="paperSize_id" class="max-w-md">
                                <option selected>Select Paper Size</option>
                                <option value="A4">A4</option>
                                <option Vaule="A5">A5</option>
                            </x-form.select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="pt-8 space-y-6 sm:pt-10 sm:space-y-5">
                <div>
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-50">
                        Book Files
                    </h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                        Upload Book Files based on the book standards.
                    </p>
                </div>
                <div class="space-y-6 sm:space-y-5">
                    <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
                        <x-jet-label value="Upload Book File" />
                        <div class="mt-1 sm:mt-0 sm:col-span-2">
                            <x-jet-input type="file" wire:model.defer="file" name="font" id="bookUrl" class="max-w-lg text-gray-500 file:mr-4 file:py-2 file:px-4
                            file:rounded-lg file:border-0
                            file:text-sm file:font-semibold
                            file:bg-blue-50 file:text-blue-700
                            hover:file:bg-blue-100 file:focus:ring-0 file:focus:border-lime-200 "
                                accept="document/pdf" />
                        </div>
                    </div>

                    <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:pt-3">
                        <x-jet-label value="Book Cover Image" />
                        <div class="mt-1 sm:mt-0 sm:col-span-2">
                            <div class="grid grid-cols-2 gap-4 items-start pt-3 ">
                                <div class="flex flex-col lg:flex-row lg:space-x-5 items-center">
                                    @if ($front_cover)
                                    <img src="{{ $front_cover->temporaryUrl() }}" alt="" srcset=""
                                        class="h-48 w-32 border-dashed border-gray-200 rounded bg-cover">
                                    @else
                                    <img src="" alt="" srcset=""
                                        class="h-48 w-32 border-dashed border-gray-200 rounded bg-cover">
                                    @endif
                                    <label for="front"
                                        class="mt-4 py-2 px-4 text-center bg-blue-50 w-32 text-blue-700 rounded-lg text-sm font-semibold hover:bg-blue-100 shadow-xl shadow-blue-100 dark:shadow-gray-700">Choose
                                        Front</label>
                                    <x-jet-input type="file" wire:model.defer="front_cover" name="font" id="front"
                                        class="hidden" accept="image/png, image/jpeg, image/jpg, image/png" />
                                </div>

                                <div class="flex flex-col lg:flex-row lg:space-x-5 items-center">
                                    @if ($back_cover)
                                    <img src="{{ $back_cover->temporaryUrl() }}" alt="" srcset=""
                                        class="h-48 w-32 border-dashed border-gray-200 rounded bg-cover">
                                    @else
                                    <img src="" alt="" srcset=""
                                        class="h-48 w-32 border-dashed border-gray-200 rounded bg-cover">
                                    @endif

                                    <label for="back"
                                        class="mt-4 py-2 px-4 text-center bg-blue-50 w-32 text-blue-700 rounded-lg text-sm font-semibold hover:bg-blue-100 shadow-xl shadow-blue-100 dark:shadow-gray-700">Choose
                                        Back</label>
                                    <x-jet-input wire:model="back_cover" type="file" name="font" id="back"
                                        class="hidden" accept="image/png, image/jpeg, image/jpg, image/png" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </x-form.card>
</div>