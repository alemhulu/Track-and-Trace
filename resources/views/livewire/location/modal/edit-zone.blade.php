<div>
    <x-data-table.modal name="editZone" maxWidth="md">
        <x-slot name="title">
            Edit Zone
        </x-slot>

        <x-slot name="body">
            <div>
                <x-jet-label for="country_id" value=" {{__('Country')}}" />
                <x-form.select wire:model="zone.country_id" id="country_id">
                    <option value=""> {{__('Select Country') }}</option>
                    @foreach ($countries as $country)
                    <option value={{ $country->id }}>{{ $country->name }}</option>
                    @endforeach
                </x-form.select>
                <x-jet-input-error for="zone.country_id" alert="Select Country" />
            </div>

            <div>
                <x-jet-label for="region_id" value=" {{__('Region / City')}}" />
                <x-form.select wire:model="zone.region_id" id="region_id" class="block w-full">
                    <option value=""> {{__('Select Region') }}</option>
                    @foreach ($regions as $region)
                    <option value={{ $region->id }}>{{ $region->name }}</option>
                    @endforeach
                </x-form.select>
                <x-jet-input-error for="zone.region_id" alert="Select Region" />
            </div>

            <div>
                <x-jet-label for="is_subcity" value="{{ __('Type') }}" />
                <div class="flex justify-between space-x-3">
                    <x-form.radio value="0" wire:model.defer="zone.is_subcity" label="Zone" name="is_subcity"
                        id="ist_zone" />

                    <x-form.radio value="1" wire:model.defer="zone.is_subcity" label="Sub-City" name="is_subcity"
                        id="ist_subcity" />
                </div>
                <x-jet-input-error for="is_subcity" alert="Zone or Sub-City Type" />
            </div>

            <div>
                <x-jet-label for="name" value="{{ __('Zone / Sub-City') }}" />
                <x-jet-input type="text" wire:model.defer="zone.name" placeholder="Type Zone or Sub-City Name" />
                <x-jet-input-error for="zone.name" alert="Zone or Sub-City Name" />
            </div>
        </x-slot>
    </x-data-table.modal>
</div>