<div>
    {{-- The Master doesn't talk, he acts. --}}
    <x-form.card function="addGrade" title="Add New Grade ">
        <div class="max-w-lg">
            <x-jet-label for="name" value="{{ __('Name') }}" />
            <x-jet-input type="text" wire:model.defer="name" placeholder="Grade Name" />
            <x-jet-input-error for="name" alert="Grade Name" />
        </div>

        <div class="max-w-lg">
            <x-jet-label for="code" value="{{ __('Code') }}" />
            <x-jet-input type="text" wire:model.defer="code" placeholder="Type Code" />
            <x-jet-input-error for="code" alert="Grade Code" />
        </div>

        <div class="max-w-2xl">
            <x-jet-label for="description" value="{{ __('Description') }}" />
            <x-form.textarea name="description" wire:model.defer="description" placeholder="Type Description" row="3" />
            <x-jet-input-error for="de" alert="Grade Description" />
        </div>
    </x-form.card>
</div>