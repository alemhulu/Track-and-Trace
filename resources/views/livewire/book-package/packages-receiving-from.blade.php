<x-data-table.modal name="receivingForm" maxWidth="2xl">
    <x-slot name="title">
        Packages Request Receiving Form
    </x-slot>

    <x-slot name="body">
        <div class="flex flex-auto lg:justify-between flex-wrap space-x-2 gap-5 items-center border-b pb-5">
            <div class="flex-grow max-w-xs flex-initial">
                <x-jet-label class="text-gray-400" value="Packages Info"></x-jet-label>
                <x-stat.package-info batch="PO10387618" quantity="50" />
            </div>

            <div class="">
                <x-jet-label class="text-gray-400" value="Sender"></x-jet-label>
                <x-organization.info h=12 image="{{ Auth::user()->profile_photo_url }}" name="Abebe Kebede"
                    email="abe@kebede.com" phone="0987654312" />
            </div>

            <div class="">
                <x-jet-label class="text-gray-400" value="Reciver"></x-jet-label>
                <x-organization.info h=12 image="{{ Auth::user()->profile_photo_url }}" name="Abebe Kebede"
                    email="abe@kebede.com" phone="0987654312" />
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 ">
            <div class="sm:grid sm:grid-cols-1 ">
                <x-jet-label value="Grade" />
                <x-form.select wire:model="grade_id" id="grade_id" class="max-w-md">
                    <option>grade</option>
                    <option>10</option>
                    <option>12</option>
                </x-form.select>
            </div>

            <div class="sm:grid sm:grid-cols-1">
                <x-jet-label value="Subject" />
                <x-form.select wire:model="subject_id" id="subject_id" class="max-w-md">
                    <option>subject</option>
                    <option>Maths</option>
                    <option>Biology</option>
                </x-form.select>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
            <div class="sm:grid sm:grid-cols-1 ">
                <x-jet-label value="Packages Batch Number" />
                <x-jet-input type="text" name="batch_number" id="batch_number" class="max-w-xl"
                    placeholder="Type Batch Number" />
            </div>

            <div class="sm:grid sm:grid-cols-1">
                <x-jet-label value="Packages Quantity" />
                <div class="mt-1 sm:mt-0 sm:col-span-2">
                    <x-jet-input type="number" name="quantity" id="quantity" class="max-w-xl"
                        placeholder="Type Quantity" />
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
            <div class="col-span-2">
                <x-jet-label class="text-gray-400" value="Packages QRs Code Range" />
            </div>
            <div class="sm:grid sm:grid-cols-1 ">
                <x-jet-label value="From" />
                <x-jet-input type="number" name="to" id="to" class="max-w-xl" placeholder="QR Code Start " />
            </div>

            <div class="sm:grid sm:grid-cols-1">
                <x-jet-label value="To" />
                <x-jet-input type="number" name="to" id="to" class="max-w-xl" placeholder="QR Code End" />
            </div>
        </div>

        <div>
            <x-jet-label for="description" value="{{ __('Remark') }}" />
            <x-form.textarea name="description" wire:model.defer="description" placeholder="Type Description" rows=4 />
            <x-jet-input-error for="de" alert="Store Description" />
        </div>

    </x-slot>
</x-data-table.modal>