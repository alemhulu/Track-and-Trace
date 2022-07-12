<div>
    <x-data-table.modal name="editRegion" maxWidth="md">
        <x-slot name="title">
            Region Edit
        </x-slot>

        <x-slot name="body">
            <div>
                <x-jet-label for="country_id" value=" {{__('Select Country')}}" />
                <x-form.select wire:model="region.country_id" id="country_id">
                    <option value=""> {{__('Country') }}</option>
                    @foreach ($countries as $country)
                    <option value="{{ $country->id }}">{{ $country->name }}</option>
                    @endforeach
                </x-form.select>
                <x-jet-input-error for="country_id" alert="Select Country" />
            </div>

            <div>
                <x-jet-label for="region.is_city" value="{{ __('Type') }}" />
                <div class="flex justify-between space-x-3">
                    <x-form.radio value="0" type="text" wire:model.defer="region.is_city" label="Region"
                        name="region.is_city" id="region" />

                    <x-form.radio value="1" type="text" wire:model.defer="region.is_city" label="City"
                        name="region.is_city" id="city" />
                </div>
                <x-jet-input-error for="region.is_city" alert="Region or City Type" />
            </div>

            <div>
                <x-jet-label for="region.name" value="{{ __('Region / City') }}" />
                <x-jet-input type="text" wire:model.defer="region.name" />
                <x-jet-input-error for="region.name" alert="Rgion or City Name" />
            </div>
        </x-slot>
    </x-data-table.modal>
</div>