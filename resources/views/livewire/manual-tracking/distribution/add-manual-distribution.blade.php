<div>
    <x-form.card function="saveDistribution" title="Add Manual Distribution" submitLabel="Record">
        <div class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <x-jet-label for="manual_book_id" value="Book" />
                    <x-form.select wire:model="manual_book_id" id="manual_book_id" class="mt-1 block w-full">
                        <option value="">Select Book</option>
                        @foreach ($books as $book)
                        <option value="{{ $book->id }}">{{ $book->title }}</option>
                        @endforeach
                    </x-form.select>
                    <x-jet-input-error for="manual_book_id" class="mt-2" />
                </div>

                <div>
                    <x-jet-label for="manual_book_package_id" value="Package (Optional)" />
                    <x-form.select wire:model.defer="manual_book_package_id" id="manual_book_package_id"
                        class="mt-1 block w-full">
                        <option value="">Select Package</option>
                        @foreach ($packages as $package)
                        <option value="{{ $package->id }}">{{ $package->package_code }} (Bal:
                            {{ $package->current_balance }})</option>
                        @endforeach
                    </x-form.select>
                    <x-jet-input-error for="manual_book_package_id" class="mt-2" />
                </div>

                <div>
                    <x-jet-label for="quantity" value="Quantity" />
                    <x-jet-input wire:model.defer="quantity" id="quantity" type="number" min="1"
                        class="mt-1 block w-full" />
                    <x-jet-input-error for="quantity" class="mt-2" />
                </div>
            </div>

            <div>
                <h3 class="font-semibold text-base text-gray-700 dark:text-gray-200">Source Location</h3>
                <div class="mt-3 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <x-jet-label for="organization_id" value="Organization" />
                        <x-form.select wire:model.defer="organization_id" id="organization_id"
                            class="mt-1 block w-full">
                            <option value="">Select Organization</option>
                            @foreach ($organizations as $organization)
                            <option value="{{ $organization->id }}">{{ $organization->name }}</option>
                            @endforeach
                        </x-form.select>
                    </div>
                    <div>
                        <x-jet-label for="country_id" value="Country" />
                        <x-form.select wire:model.defer="country_id" id="country_id" class="mt-1 block w-full">
                            <option value="">Select Country</option>
                            @foreach ($countries as $country)
                            <option value="{{ $country->id }}">{{ $country->name }}</option>
                            @endforeach
                        </x-form.select>
                    </div>
                    <div>
                        <x-jet-label for="region_id" value="Region" />
                        <x-form.select wire:model.defer="region_id" id="region_id" class="mt-1 block w-full">
                            <option value="">Select Region</option>
                            @foreach ($regions as $region)
                            <option value="{{ $region->id }}">{{ $region->name }}</option>
                            @endforeach
                        </x-form.select>
                    </div>
                    <div>
                        <x-jet-label for="zone_id" value="Zone" />
                        <x-form.select wire:model.defer="zone_id" id="zone_id" class="mt-1 block w-full">
                            <option value="">Select Zone</option>
                            @foreach ($zones as $zone)
                            <option value="{{ $zone->id }}">{{ $zone->name }}</option>
                            @endforeach
                        </x-form.select>
                    </div>
                    <div>
                        <x-jet-label for="woreda_id" value="Woreda" />
                        <x-form.select wire:model.defer="woreda_id" id="woreda_id" class="mt-1 block w-full">
                            <option value="">Select Woreda</option>
                            @foreach ($woredas as $woreda)
                            <option value="{{ $woreda->id }}">{{ $woreda->name }}</option>
                            @endforeach
                        </x-form.select>
                    </div>
                </div>
            </div>

            <div>
                <h3 class="font-semibold text-base text-gray-700 dark:text-gray-200">Destination Location</h3>
                <div class="mt-3 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <x-jet-label for="destination_organization_id" value="Destination Organization" />
                        <x-form.select wire:model.defer="destination_organization_id" id="destination_organization_id"
                            class="mt-1 block w-full">
                            <option value="">Select Organization</option>
                            @foreach ($organizations as $organization)
                            <option value="{{ $organization->id }}">{{ $organization->name }}</option>
                            @endforeach
                        </x-form.select>
                    </div>
                    <div>
                        <x-jet-label for="destination_country_id" value="Destination Country" />
                        <x-form.select wire:model.defer="destination_country_id" id="destination_country_id"
                            class="mt-1 block w-full">
                            <option value="">Select Country</option>
                            @foreach ($countries as $country)
                            <option value="{{ $country->id }}">{{ $country->name }}</option>
                            @endforeach
                        </x-form.select>
                    </div>
                    <div>
                        <x-jet-label for="destination_region_id" value="Destination Region" />
                        <x-form.select wire:model.defer="destination_region_id" id="destination_region_id"
                            class="mt-1 block w-full">
                            <option value="">Select Region</option>
                            @foreach ($regions as $region)
                            <option value="{{ $region->id }}">{{ $region->name }}</option>
                            @endforeach
                        </x-form.select>
                    </div>
                    <div>
                        <x-jet-label for="destination_zone_id" value="Destination Zone" />
                        <x-form.select wire:model.defer="destination_zone_id" id="destination_zone_id"
                            class="mt-1 block w-full">
                            <option value="">Select Zone</option>
                            @foreach ($zones as $zone)
                            <option value="{{ $zone->id }}">{{ $zone->name }}</option>
                            @endforeach
                        </x-form.select>
                    </div>
                    <div>
                        <x-jet-label for="destination_woreda_id" value="Destination Woreda" />
                        <x-form.select wire:model.defer="destination_woreda_id" id="destination_woreda_id"
                            class="mt-1 block w-full">
                            <option value="">Select Woreda</option>
                            @foreach ($woredas as $woreda)
                            <option value="{{ $woreda->id }}">{{ $woreda->name }}</option>
                            @endforeach
                        </x-form.select>
                    </div>
                </div>
            </div>

            <div>
                <x-jet-label for="remarks" value="Remarks" />
                <textarea wire:model.defer="remarks" id="remarks" rows="3"
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100"></textarea>
                <x-jet-input-error for="remarks" class="mt-2" />
            </div>
        </div>
    </x-form.card>
</div>
