@props(['function', 'title' ,'buttons'=> true])
<form wire:submit.prevent={{ $function }} class="h-auto">
    <div class="relative mb-10 py-6 px-2 sm:px-4 lg:py-0 lg:px-0 lg:col-span-8 bg-white sm:rounded-lg dark:bg-gray-800">
        <x-form.header>
            {{ __($title) }}
        </x-form.header>

        <div class="px-2 sm:px-4 pt-4 mb-5">
            <div class="space-y-5 ">
                {{$slot }}
            </div>
        </div>

        @if ($buttons)
        <div class="px-2 sm:px-4 py-3 text-right mb-0 space-x-4">
            <x-jet-secondary-button onclick="openTab(event,'non')" class="">
                {{__('Cancel') }}
            </x-jet-secondary-button>

            <x-jet-button type="submit" class="disabled:opacity-40 flex">
                {{__('Add') }}
                <x-action.button-loader wire:loading wire:target='{{ $function }}' />
            </x-jet-button>
        </div>
        @endif

    </div>
</form>