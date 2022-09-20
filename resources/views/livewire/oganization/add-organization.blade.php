<x-form.card function="addOrganization" title="Add New Organization">
    <form class=" space-y-8 divide-y divide-gray-200">
        <div class="space-y-8 divide-y divide-gray-200 sm:space-y-5">
            {{-- Basic Profile information --}}
            <div>
                <div>
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-50">
                        Profile
                    </h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                        Basic Profile information
                    </p>
                </div>

                <div class="mt-6 sm:mt-5 space-y-6 sm:space-y-5">
                    <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
                        <x-jet-label value="Type" />
                        <div class="mt-1 sm:mt-0 sm:col-span-2">
                            <x-form.select wire:model="type_id" id="type_id" class="max-w-md">
                                <option>Organazation</option>
                                <option>School</option>
                                <option>Campany</option>
                            </x-form.select>
                        </div>
                    </div>

                    <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start  sm:pt-3">
                        <x-jet-label value="Name" />
                        <div class="mt-1 sm:mt-0 sm:col-span-2">
                            <x-jet-input type="text" name="name" id="name" placeholder="name" class="max-w-xl" />
                        </div>
                    </div>

                    <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start  sm:pt-3">
                        <x-jet-label value="Email" />
                        <div class="mt-1 sm:mt-0 sm:col-span-2">
                            <x-jet-input type="text" name="email" id="email" placeholder="example@example.com"
                                class="max-w-xl" />
                        </div>
                    </div>

                    <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start  sm:pt-3">
                        <x-jet-label value="Telephone" />
                        <div class="mt-1 sm:mt-0 sm:col-span-2">
                            <x-jet-input type="text" name="telephone" id="telephone" placeholder="0115456790"
                                class="max-w-xl" />
                        </div>
                    </div>

                    <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start  sm:pt-3">
                        <x-jet-label value="Mobile phone" />
                        <div class="mt-1 sm:mt-0 sm:col-span-2">
                            <x-jet-input type="text" name="phone" id="phone" placeholder="0912345678"
                                class="max-w-xl" />
                        </div>
                    </div>

                    <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start  sm:pt-3">
                        <x-jet-label value="Website" />
                        <div class="mt-1 sm:mt-0 sm:col-span-2">
                            <x-jet-input type="url" name="website" id="website" placeholder="http://example.com"
                                class=" max-w-xl" />
                        </div>
                    </div>

                    <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start  sm:pt-3">
                        <x-jet-label value="Logo" />
                        <div class="mt-1 sm:mt-0 sm:col-span-2">
                            <div class="flex items-center space-x-2">
                                <span class=" shrink-0 h-16 w-16 rounded-full overflow-hidden bg-gray-100 object-cover">
                                    <img class="h-16 w-16 object-cover rounded-full" src="/logom.png"
                                        alt="Current profile photo" />
                                </span>
                                <input type="file" class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0
                                            file:text-sm file:font-semibold  file:bg-violet-50 file:text-violet-700 hover:file:bg-violet-100
                                            " />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Contact Person Information --}}
            <div class="pt-8 space-y-6 sm:pt-10 sm:space-y-5">
                <div>
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-50">
                        Contact Person Information
                    </h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                        Use a permanent address where you can receive mail.
                    </p>
                </div>
                <div class="space-y-6 sm:space-y-5">
                    <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
                        <x-jet-label value="Assign User" />
                        <div class="mt-1 sm:mt-0 sm:col-span-2">
                            <x-form.select wire:model="user_id" id="user_id" class="max-w-md">
                                <option>select user</option>
                                <option>user 1</option>
                                <option>user 2</option>
                            </x-form.select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="pt-8 space-y-6 sm:pt-10 sm:space-y-5">
                <div>
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-50">
                        Location Information
                    </h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                        Use a permanent address where you can receive mail.
                    </p>
                </div>
                <div class="space-y-6 sm:space-y-5">
                    <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
                        <x-jet-label value="Country" />
                        <div class="mt-1 sm:mt-0 sm:col-span-2">
                            <x-form.select wire:model="country_id" id="country_id" class="max-w-md">
                                <option>Organazation</option>
                                <option>School</option>
                                <option>Campany</option>
                            </x-form.select>
                        </div>
                    </div>

                    <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start  sm:pt-3">
                        <x-jet-label value="Region / City" />
                        <div class="mt-1 sm:mt-0 sm:col-span-2">
                            <x-form.select wire:model="region_id" id="region_id" class="max-w-md">
                                <option>Organazation</option>
                                <option>School</option>
                                <option>Campany</option>
                            </x-form.select>
                        </div>
                    </div>

                    <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start  sm:pt-3">
                        <x-jet-label value="Zone / Sub-City" />
                        <div class="mt-1 sm:mt-0 sm:col-span-2">
                            <x-form.select wire:model="zone_id" id="zone_id" class="max-w-md">
                                <option>Organazation</option>
                                <option>School</option>
                                <option>Campany</option>
                            </x-form.select>
                        </div>
                    </div>

                    <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start  sm:pt-3">
                        <x-jet-label value="Kebele" />
                        <div class="mt-1 sm:mt-0 sm:col-span-2">
                            <x-form.select wire:model="kebele_id" id="kebele_id" class="max-w-md">
                                <option>Organazation</option>
                                <option>School</option>
                                <option>Campany</option>
                            </x-form.select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</x-form.card>