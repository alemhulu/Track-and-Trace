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
                            {{ $package->current_balance }})
                        </option>
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
                @php
                $sourceRegionDisabled = empty($country_id) && empty($organization_id);
                $sourceZoneDisabled = empty($region_id) && empty($country_id) && empty($organization_id);
                $sourceWoredaDisabled = empty($zone_id) && empty($region_id) && empty($country_id) &&
                empty($organization_id);
                @endphp
                <h3 class="font-semibold text-base text-gray-700 dark:text-gray-200">Source Location</h3>
                <p class="mt-1 text-xs text-gray-500">Choose the source organization or a valid country → region → zone
                    → woreda chain.</p>
                <div class="mt-3 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <x-jet-label for="organization_id" value="Organization" />
                        <x-form.select wire:model="organization_id" id="organization_id" class="mt-1 block w-full">
                            <option value="">Select Organization</option>
                            @foreach ($organizations as $organization)
                            <option value="{{ $organization->id }}">{{ $organization->name }}</option>
                            @endforeach
                        </x-form.select>
                    </div>
                    <div>
                        <x-jet-label for="country_id" value="Country" />
                        <x-form.select wire:model="country_id" id="country_id" class="mt-1 block w-full">
                            <option value="">Select Country</option>
                            @foreach ($countries as $country)
                            <option value="{{ $country->id }}">{{ $country->name }}</option>
                            @endforeach
                        </x-form.select>
                    </div>
                    <div>
                        <x-jet-label for="region_id" value="Region" />
                        <select wire:model="region_id" id="region_id" class="mt-1 block w-full" {{ $sourceRegionDisabled
                            ? 'disabled' : '' }}>
                            <option value="">Select Region</option>
                            @foreach ($sourceRegions as $region)
                            <option value="{{ $region->id }}">{{ $region->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-jet-label for="zone_id" value="Zone" />
                        <select wire:model="zone_id" id="zone_id" class="mt-1 block w-full" {{ $sourceZoneDisabled
                            ? 'disabled' : '' }}>
                            <option value="">Select Zone</option>
                            @foreach ($sourceZones as $zone)
                            <option value="{{ $zone->id }}">{{ $zone->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-jet-label for="woreda_id" value="Woreda" />
                        <select wire:model="woreda_id" id="woreda_id" class="mt-1 block w-full" {{ $sourceWoredaDisabled
                            ? 'disabled' : '' }}>
                            <option value="">Select Woreda</option>
                            @foreach ($sourceWoredas as $woreda)
                            <option value="{{ $woreda->id }}">{{ $woreda->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div>
                @php
                $destinationRegionDisabled = empty($destination_country_id) && empty($destination_organization_id);
                $destinationZoneDisabled = empty($destination_region_id) && empty($destination_country_id) &&
                empty($destination_organization_id);
                $destinationWoredaDisabled = empty($destination_zone_id) && empty($destination_region_id) &&
                empty($destination_country_id) && empty($destination_organization_id);
                @endphp
                <h3 class="font-semibold text-base text-gray-700 dark:text-gray-200">Destination Location</h3>
                <p class="mt-1 text-xs text-gray-500">Select a valid destination organization or country → region → zone
                    → woreda chain.</p>
                <div class="mt-3 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <x-jet-label for="destination_organization_id" value="Destination Organization" />
                        <x-form.select wire:model="destination_organization_id" id="destination_organization_id"
                            class="mt-1 block w-full">
                            <option value="">Select Organization</option>
                            @foreach ($organizations as $organization)
                            <option value="{{ $organization->id }}">{{ $organization->name }}</option>
                            @endforeach
                        </x-form.select>
                    </div>
                    <div>
                        <x-jet-label for="destination_country_id" value="Destination Country" />
                        <x-form.select wire:model="destination_country_id" id="destination_country_id"
                            class="mt-1 block w-full">
                            <option value="">Select Country</option>
                            @foreach ($countries as $country)
                            <option value="{{ $country->id }}">{{ $country->name }}</option>
                            @endforeach
                        </x-form.select>
                    </div>
                    <div>
                        <x-jet-label for="destination_region_id" value="Destination Region" />
                        <select wire:model="destination_region_id" id="destination_region_id" class="mt-1 block w-full"
                            {{ $destinationRegionDisabled ? 'disabled' : '' }}>
                            <option value="">Select Region</option>
                            @foreach ($destinationRegions as $region)
                            <option value="{{ $region->id }}">{{ $region->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-jet-label for="destination_zone_id" value="Destination Zone" />
                        <select wire:model="destination_zone_id" id="destination_zone_id" class="mt-1 block w-full" {{
                            $destinationZoneDisabled ? 'disabled' : '' }}>
                            <option value="">Select Zone</option>
                            @foreach ($destinationZones as $zone)
                            <option value="{{ $zone->id }}">{{ $zone->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-jet-label for="destination_woreda_id" value="Destination Woreda" />
                        <select wire:model="destination_woreda_id" id="destination_woreda_id" class="mt-1 block w-full"
                            {{ $destinationWoredaDisabled ? 'disabled' : '' }}>
                            <option value="">Select Woreda</option>
                            @foreach ($destinationWoredas as $woreda)
                            <option value="{{ $woreda->id }}">{{ $woreda->name }}</option>
                            @endforeach
                        </select>
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
