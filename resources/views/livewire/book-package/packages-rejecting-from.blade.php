<x-data-table.modal name="reasonForm" maxWidth="2xl">
    <x-slot name="title">
        Reason For Rejecting Request
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

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 gap-x-6">
            <x-multi-checkbox name="wrong" value="Wrong Request" title="Wrong Request" checked="" />
            <x-multi-checkbox name="unmatch" value="Unmatched Request" title="Unmatched Request" checked="" />
            <x-multi-checkbox name="wrong" value="Wrong Subject" title="Wrong Subject" checked="" />
            <x-multi-checkbox name="wrong" value="Wrong Grade" title="Wrong Grade" checked="" />
            <x-multi-checkbox name="unmatch" value="Packages Problem" title="Packages Problem" checked="" />
            <x-multi-checkbox name="wrong" value="Wrong Batch" title="Wrong Batch" checked="" />
            <x-multi-checkbox name="unmatch" value="Fake Request" title="Fake Request" checked="" />
            <x-multi-checkbox name="unmatch" value="Sender Asks" title="Sender Asks" checked="" />
        </div>

        <div>
            <x-jet-label for="description" value="{{ __('Remark') }}" />
            <x-form.textarea name="description" wire:model.defer="description" placeholder="Type Description" rows=4 />
            <x-jet-input-error for="de" alert="Store Description" />
        </div>

    </x-slot>
</x-data-table.modal>