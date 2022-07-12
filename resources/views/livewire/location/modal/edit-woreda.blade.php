<div>
    <x-data-table.modal name="editWoreda" maxWidth="md">
        <x-slot name="title">
            Edit Woreda
        </x-slot>

        <x-slot name="body">
            <div>
                <x-jet-label for="country_id" value=" {{__('Select Country')}}" />
                <x-form.select wire:model="woreda.country_id" id="country_id">
                    <option value=""> {{__('Country') }}</option>
                    @foreach ($countries as $country)
                    <option value={{ $country->id }}>{{ $country->name }}</option>
                    @endforeach
                </x-form.select>
                <x-jet-input-error for="woreda.country_id" alert="Select Country" />
            </div>

            <div>
                <x-jet-label for="region_id" value=" {{__('Region / City')}}" />
                <x-form.select wire:model="woreda.region_id" id="region_id">
                    <option value=""> {{__('Select Region') }}</option>
                    @foreach ($regions as $region)
                    <option value={{ $region->id }}>{{ $region->name }}</option>
                    @endforeach
                </x-form.select>
                <x-jet-input-error for="woreda.region_id" alert="Select Region" />
            </div>

            <div>
                <x-jet-label for="zone_id" value=" {{__('Zone / Sub-City')}}" />
                <x-form.select wire:model="woreda.zone_id" id="zone_id">
                    <option value=""> {{__('Select Zone') }}</option>
                    @foreach ($zones as $zone)
                    <option value={{ $zone->id }}>{{ $zone->name }}</option>
                    @endforeach
                </x-form.select>
                <x-jet-input-error for="woreda.zone_id" alert="Select Zone" />
            </div>

            <div>
                <x-jet-label for="name" value="{{ __('Woreda') }}" />
                <x-jet-input type="text" wire:model.defer="woreda.name" />
                <x-jet-input-error for="woreda.name" alert="Woreda Name" />
            </div>
        </x-slot>
    </x-data-table.modal>
</div>