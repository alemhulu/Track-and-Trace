<x-data-table.modal name="editCountry" maxWidth="sm">
    <x-slot name="title">
        Country Edit
    </x-slot>

    <x-slot name="body">
        <div>
            <x-jet-label for="name" value="{{ __('Country') }}" />
            <x-jet-input type="text" wire:model.defer="country.name" />
            <x-jet-input-error for="country.name" alert="Country Name" />
        </div>
    </x-slot>
</x-data-table.modal>