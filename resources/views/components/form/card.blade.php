@props(['function', 'title' ])
<form wire:submit.prevent={{ $function }} class="h-auto">
    <div class="relative mb-10 py-6 px-5 sm:px-6 lg:py-0 lg:px-0 lg:col-span-8 bg-white sm:rounded-lg">
        <x-form.header>
            {{ __($title) }}
        </x-form.header>

        <div class="px-4 pt-4 mb-5">
            <div class="space-y-5 ">
                {{$slot }}
            </div>
        </div>

        <div class="px-4 py-3 text-right sm:px-6 mb-0">
            <x-jet-secondary-button onclick="openTab(event,'non')" class="">
                {{__('Cancel') }}
            </x-jet-secondary-button>
            <x-jet-button type="submit" class="ml-4 disabled:opacity-40 flex">
                {{__('Add') }}
                <x-action.button-loader wire:loading wire:target='{{ $function }}' />
            </x-jet-button>
        </div>
    </div>
</form>