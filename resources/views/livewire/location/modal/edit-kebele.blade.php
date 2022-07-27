<div>
    <x-data-table.modal name="editKebele" maxWidth="md">
        <x-slot name="title">
            Edit Kebele
        </x-slot>

        <x-slot name="body">
            <div>
                <x-jet-label for="country_id" value=" {{__('Select Country')}}" />
                <x-form.select wire:model="kebele.country_id" id="country_id">
                    <option value=""> {{__('Country') }}</option>
                    @foreach ($countries as $country)
                    <option value={{ $country->id }}>{{ $country->name }}</option>
                    @endforeach
                </x-form.select>
                <x-jet-input-error for="kebele.country_id" alert="Select Country" />
            </div>

            <div>
                <x-jet-label for="region_id" value=" {{__('Region / City')}}" />
                <x-form.select wire:model="kebele.region_id" id="region_id">
                    <option value=""> {{__('Select Region') }}</option>
                    @foreach ($regions as $region)
                    <option value={{ $region->id }}>{{ $region->name }}</option>
                    @endforeach
                </x-form.select>
                <x-jet-input-error for="kebele.region_id" alert="Select Region" />
            </div>

            <div>
                <x-jet-label for="zone_id" value=" {{__('Zone / Sub-City')}}" />
                <x-form.select wire:model="kebele.zone_id" id="zone_id">
                    <option value=""> {{__('Select Zone') }}</option>
                    @foreach ($zones as $zone)
                    <option value={{ $zone->id }}>{{ $zone->name }}</option>
                    @endforeach
                </x-form.select>
                <x-jet-input-error for="kebele.zone_id" alert="Select Zone" />
            </div>

            <div>
                <x-jet-label for="woreda_id" value=" {{__('Woreda')}}" />
                <x-form.select wire:model="kebele.woreda_id" id="woreda_id">
                    <option value=""> {{__('Select Woreda') }}</option>
                    @foreach ($woredas as $woreda)
                    <option value={{ $woreda->id }}>{{ $woreda->name }}</option>
                    @endforeach
                </x-form.select>
                <x-jet-input-error for="kebele.woreda_id" alert="Select Woreda" />
            </div>

            <div>
                <x-jet-label for="name" value="{{ __('Kebele') }}" />
                <x-jet-input type="text" wire:model.defer="kebele.name" />
                <x-jet-input-error for="kebele.name" alert="Kebele Name" />
            </div>
        </x-slot>
    </x-data-table.modal>
</div>