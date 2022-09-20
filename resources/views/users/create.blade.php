<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:text-gray-200">
            {{ __('Users Management') }}
        </h2>
    </x-slot>

    <x-slot name="actionButton">
        <a href="{{ route('users.index') }}">
            <x-button class="flex ">
                <i class="flex fi-rr-arrow-left mr-2"></i>
                {{ __('Back') }}
            </x-button>
        </a>
    </x-slot>

    <x-form-card action="{{ route('users.store') }}" title="Create New Catagory">
        <x-slot name="body">
            <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:border-gray-200 sm:pt-5">
                <x-label for="name" value="Name" />
                <div class="mt-1 sm:mt-0 sm:col-span-2">
                    <x-input type="text" name="name" id="name" />
                    <x-input-error for="name" />
                </div>
            </div>

            <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:border-gray-200 sm:pt-5">
                <x-label for="email" value="Email" />
                <div class="mt-1 sm:mt-0 sm:col-span-2">
                    <x-input type="text" name="email" id="email" />
                    <x-input-error for="email" />
                </div>
            </div>

            <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:border-gray-200 sm:pt-5">
                <x-label for="password" value="Password" />
                <div class="mt-1 sm:mt-0 sm:col-span-2">
                    <x-input type="password" name="password" id="password" />
                    <x-input-error for="password" />
                </div>
            </div>

            <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:border-gray-200 sm:pt-5">
                <x-label for="confirm-password" value="Password Confirmation" />
                <div class="mt-1 sm:mt-0 sm:col-span-2">
                    <x-input type="password" name="confirm-password" id="confirm-password" />
                    <x-input-error for="confirm-password" />
                </div>
            </div>

            <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:border-gray-200 sm:pt-5">
                <x-label for="title" value="Assign Roles" />
                <div class="mt-1 sm:col-span-2 border pt-1 pb-3 px-3 rounded-md border-gray-300">
                    <p class="text-gray-400 text-xs font-semibold">select one or more</p>
                    <div class="sm:grid sm:grid-cols-4 gap-2">
                        @foreach ($roles as $role)
                        <x-multi-checkbox name="roles[]" value="{{ $role }}" title="{{ $role }}" />
                        @endforeach
                    </div>
                    <x-input-error for="roles[]" />
                </div>
            </div>
        </x-slot>
    </x-form-card>
</x-app-layout>