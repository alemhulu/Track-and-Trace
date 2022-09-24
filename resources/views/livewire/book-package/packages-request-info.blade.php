<div>
    <x-form.card title="Packages Request Info" buttons="">

        <div class="flex flex-auto lg:justify-between flex-wrap space-x-2 gap-5 items-center">
            <div class="">
                <x-jet-label class="text-gray-400" value="Book"></x-jet-label>
                <x-book.book-info image="https://i.ytimg.com/vi/NNKPR6nICJI/maxresdefault.jpg" grade="Grade 10"
                    subject="Biology" type="Student Text Book" edition="1st Edition 2013" ISBN="4820715" />
            </div>

            <div class="flex-grow max-w-xs flex-initial">
                <x-jet-label class="text-gray-400" value="Packages Info"></x-jet-label>
                <x-stat.package-info batch="PO10387618" quantity="50" />
            </div>

            <div class="">
                <x-jet-label class="text-gray-400" value="Organization"></x-jet-label>
                <x-organization.info image="logom.png" name="Ministry of Education" email="mail@ministry.com"
                    phone="0987654321" />
            </div>

            <div class="">
                <x-jet-label class="text-gray-400" value="Contact Person"></x-jet-label>
                <x-organization.info h=12 image="{{ Auth::user()->profile_photo_url }}" name="Abebe Kebede"
                    email="abe@kebede.com" phone="0987654312" />
            </div>

            <div class="">
                <x-jet-label class="text-gray-400" value="Status"></x-jet-label>
                <x-button btnType="warning" class="animate-pulse hover:animate-none">Pending</x-button>
            </div>
        </div>

        <div class="pb-4 flex justify-between">
            <div class="">
                <x-jet-label class="text-gray-400" value="Request Date"></x-jet-label>
                <div class="flex items-center gap-2 text-gray-600 dark:text-gray-300">
                    <i class="flex text-2xl fi fi-rr-time-check"></i>
                    <span class="text-md font-semibold "> July 10, 2015</span>
                </div>
            </div>

            <div class="space-x-2">
                <x-button type="button" btnType="secondary">CANCEL</x-button>
                <x-button type="button" btnType="danger">REJECT</x-button>
                <x-button type="button" btnType="success" disabled>ACCEPT</x-button>
            </div>
        </div>
    </x-form.card>

    <livewire:book-package.packages-request-list>
</div>