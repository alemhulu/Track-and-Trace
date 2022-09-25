<x-data-table.modal name="editType" maxWidth="sm">
    <x-slot name="title">
        OrganizationType Edit
    </x-slot>

    <x-slot name="body">
        <div>
            <x-jet-label for="name" value="{{ __('OrganizationType Name') }}" />
            <x-jet-input type="text" wire:model.defer="organizationtype.name" />
            <x-jet-input-error for="organizationtype.name" alert="OrganizationType Name" />
        </div>

        <div>
            <x-jet-label for="code" value="{{ __('Code') }}" />
            <x-jet-input type="number" wire:model.defer="Code" />
            <x-jet-input-error for="organizationtype.code" alert="OrganizationType Code" />
        </div>

        <div>
            <x-jet-label for="description" value="{{ __('Description') }}" />
            <x-jet-input type="text" wire:model.defer="Description" />
            <x-jet-input-error for="organizationtype.description" alert="OrganizationType Description" />
        </div>
    </x-slot>
</x-data-table.modal>