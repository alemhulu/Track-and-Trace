<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Users Management') }}
        </h2>
    </x-slot>

    <x-slot name="actionButton">
        <a href="{{ route('roles.index') }}">
            <x-button class="flex ">
                <i class="flex fi-rr-arrow-left mr-2"></i>
                {{ __('Back') }}
            </x-button>
        </a>
    </x-slot>

    <x-form-card action="{{ route('roles.store') }}" title="Create New Role">
        <x-slot name="body">
            <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:border-gray-200 sm:pt-5">
                <x-label for="name" value="Name" />
                <div class="mt-1 sm:mt-0 sm:col-span-2">
                    <x-input type="text" name="name" id="name" />
                    <x-input-error for="name" />
                </div>
            </div>

            <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:border-gray-200 sm:pt-5 mt-2">
                <x-label for="title" value="Permissions" />
                <div class="mt-1 sm:col-span-2 border pt-1 pb-3 px-3 rounded-md border-gray-300">
                    <p class="text-gray-400 text-xs font-semibold">select one or more</p>
                    <div class="sm:grid sm:grid-cols-4 gap-2">
                        @foreach ($permission as $value)
                        <x-multi-checkbox name="permission[]" value="{{ $value->id }}" title="{{ $value->name }}" />
                        @endforeach
                    </div>
                    <x-input-error for="permission" />
                </div>
            </div>
        </x-slot>
    </x-form-card>
</x-app-layout>