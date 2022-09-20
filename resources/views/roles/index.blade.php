<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:text-gray-200">
            {{ __('Role Management') }}
        </h2>
    </x-slot>

    <x-slot name="actionButton">
        <a href="{{ route('roles.create') }}">
            <x-button class="flex ">
                <i class="flex fi fi-rr-plus mr-2"></i>
                {{ __('Create New Role') }}
            </x-button>
        </a>
    </x-slot>

    <div class="py-3">
        <div class="mx-auto">
            <x-form.table title="Users List">
                <x-slot name="tableHeaders">
                    <x-data-table.th scope="col"> {{__('#') }}</x-data-table.th>
                    <x-data-table.th scope="col"> {{__('Name') }}</x-data-table.th>
                    <x-data-table.th scope="col"> {{__('Permissions') }}</x-data-table.th>
                    <x-data-table.th scope="col" class="sr-only">{{__('Action') }}</x-data-table.th>
                </x-slot>

                <x-slot name="tableRows">
                    @forelse($roles as $key => $role)
                    <x-data-table.tr>
                        <x-td>
                            <div class="text-sm text-gray-700 dark:text-gray-100 font-semibold">{{ ++$i}}</div>
                        </x-td>

                        <x-td>
                            <div class="text-sm text-gray-700 dark:text-gray-100 font-semibold">{{ $role->name }}</div>
                        </x-td>

                        <x-td>
                            <div
                                class="flex text-sm p-2 text-white bg-blue-500 rounded-full justify-center items-center w-9 h-9">
                                {{ $role->permissions->count() }}
                            </div>
                        </x-td>

                        <x-td>
                            <x-action {{-- view="{{ route('roles.show',$role->id) }}" --}}
                                edit="{{ route('roles.edit', $role->id) }}"
                                delete="{{ route('roles.destroy', $role->id) }}" />
                        </x-td>
                    </x-data-table.tr>
                    @empty
                    <x-empty-row colspan=4 />
                    @endforelse
                </x-slot>
            </x-form.table>
        </div>
        {!! $roles->render() !!}
    </div>
</x-app-layout>