<x-form.card function="addOrganization" title="Add New Organization">
    <form class=" space-y-8 divide-y divide-gray-200">
        <div class="space-y-8 divide-y divide-gray-200 sm:space-y-5">
            {{-- Basic Profile information --}}
            <div>
                <x-form.section title="Profile" subtitle="Basic Organization Profile information" />

                <div class="mt-6 sm:mt-5 space-y-6 sm:space-y-5">
                    <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
                        <x-jet-label value="Type" />
                        <div class="mt-1 sm:mt-0 sm:col-span-2">
                            <x-form.select wire:model="organization_type_id" class="max-w-md">
                                <option value="" selected>select</option>
                                @forelse ( $organizationTypes as $type )
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @empty
                                <option disabled>nothing to select !</option>
                                @endforelse
                            </x-form.select>
                        </div>
                    </div>

                    <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start  sm:pt-3">
                        <x-jet-label value="Name" />
                        <div class="mt-1 sm:mt-0 sm:col-span-2">
                            <x-jet-input type="text" wire:model="name" placeholder="name" class="max-w-xl" />
                        </div>

                    </div>

                    <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start  sm:pt-3">
                        <x-jet-label value="Email" />
                        <div class="mt-1 sm:mt-0 sm:col-span-2">
                            <x-jet-input type="email" wire:model="email" placeholder="example@example.com"
                                class="max-w-xl" />
                        </div>
                    </div>

                    <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start  sm:pt-3">
                        <x-jet-label value="Telephone" />
                        <div class="mt-1 sm:mt-0 sm:col-span-2">
                            <x-jet-input type="number" wire:model="telephone" placeholder="0115456790"
                                class="max-w-xl" />
                        </div>
                    </div>

                    <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start  sm:pt-3">
                        <x-jet-label value="Mobile phone" />
                        <div class="mt-1 sm:mt-0 sm:col-span-2">
                            <x-jet-input type="number" wire:model="mobile" placeholder="0912345678" class="max-w-xl" />
                        </div>
                    </div>

                    <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start  sm:pt-3">
                        <x-jet-label value="Website" />
                        <div class="mt-1 sm:mt-0 sm:col-span-2">
                            <x-jet-input type="url" wire:model="website" placeholder="http://example.com"
                                class=" max-w-xl" />
                        </div>
                    </div>

                    <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start  sm:pt-3">
                        <x-jet-label value="Logo" />
                        <div class="mt-1 sm:mt-0 sm:col-span-2">
                            <x-form.image-upload wire:model="logo" :preview="$logo" />
                        </div>
                    </div>
                </div>
            </div>

            {{-- Contact Person Information --}}
            <div class="pt-8 space-y-6 sm:pt-10 sm:space-y-5">
                <x-form.section title="Contact Person Information"
                    subtitle=" Use a permanent address where you can receive mail." />

                <div class="space-y-6 sm:space-y-5">
                    <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
                        <x-jet-label value="Assign User" />
                        <div class="mt-1 sm:mt-0 sm:col-span-2">
                            <x-user.select :users="$users" :select="$select" :hide="$hide" :user="$user" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="pt-8 space-y-6 sm:pt-10 sm:space-y-5">
                <x-form.section title="Location Information"
                    subtitle="Use a permanent address where you can receive mail." />

                <div class="space-y-6 sm:space-y-5">
                    <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
                        <x-jet-label value="Country" />
                        <div class="mt-1 sm:mt-0 sm:col-span-2">
                            <x-form.select wire:model="country_id" id="country_id" class="max-w-md">
                                <option>select</option>
                                @forelse ( $countries as $country )
                                <option value="{{ $country->id }}">{{ $country->name }}</option>
                                @empty
                                <option disabled>nothing to select !</option>
                                @endforelse
                            </x-form.select>
                        </div>
                    </div>

                    <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start  sm:pt-3">
                        <x-jet-label value="Region / City" />
                        <div class="mt-1 sm:mt-0 sm:col-span-2">
                            <x-form.select wire:model="region_id" id="region_id" class="max-w-md">
                                <option value="" selected>select</option>
                                @forelse ( $regions as $region )
                                <option value="{{ $region->id }}">{{ $region->name }}</option>
                                @empty
                                <option disabled>nothing to select !</option>
                                @endforelse
                            </x-form.select>
                        </div>
                    </div>

                    <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start  sm:pt-3">
                        <x-jet-label value="Zone / Sub-City" />
                        <div class="mt-1 sm:mt-0 sm:col-span-2">
                            <x-form.select wire:model="zone_id" id="zone_id" class="max-w-md">
                                <option value="" selected>select</option>
                                @forelse ( $zones as $zone)
                                <option value="{{ $zone->id }}">{{ $zone->name }}</option>
                                @empty
                                <option disabled>nothing to select !</option>
                                @endforelse
                            </x-form.select>
                        </div>
                    </div>

                    <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start  sm:pt-3">
                        <x-jet-label value="Woreda" />
                        <div class="mt-1 sm:mt-0 sm:col-span-2">
                            <x-form.select wire:model="woreda_id" id="woreda_id" class="max-w-md">
                                <option value="" selected>select</option>
                                @forelse ( $woredas as $woreda )
                                <option value="{{ $woreda->id }}">{{ $woreda->name }}</option>
                                @empty
                                <option disabled>nothing to select !</option>
                                @endforelse
                            </x-form.select>
                        </div>
                    </div>

                    <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start  sm:pt-3">
                        <x-jet-label value="Kebele" />
                        <div class="mt-1 sm:mt-0 sm:col-span-2">
                            <x-form.select wire:model="kebele_id" id="kebele_id" class="max-w-md">
                                <option value="" selected>select</option>
                                @forelse ( $kebeles as $kebele )
                                <option value="{{ $kebele->id }}">{{ $kebele->name }}</option>
                                @empty
                                <option disabled>nothing to select !</option>
                                @endforelse
                            </x-form.select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</x-form.card>