<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:text-gray-200">
            {{ __('Users Management') }}
        </h2>
    </x-slot>

    <x-slot name="actionButton">
        <a href="{{ route('users.create') }}">
            <x-button class="flex">
                <i class="flex fi fi-rr-plus mr-2"></i>
                {{ __('Create New User') }}
            </x-button>
        </a>
    </x-slot>

    <div class="py-3">
        <div class="mx-auto">
            <x-form.table title="Users List">
                <x-slot name="tableHeaders">
                    <x-data-table.th scope="col">#</x-data-table.th>
                    <x-data-table.th scope="col"> {{__('Name') }}</x-data-table.th>
                    <x-data-table.th scope="col"> {{__('Email') }}</x-data-table.th>
                    <x-data-table.th scope="col"> {{__('Roles') }}</x-data-table.th>
                    <x-data-table.th scope="col" class="sr-only">{{__('Action') }}</x-data-table.th>
                </x-slot>

                <x-slot name="tableRows">
                    @forelse($data as $key => $user)
                    <x-data-table.tr>
                        <x-data-table.td>
                            <div class="text-sm text-gray-700 dark:text-gray-100 font-semibold">{{ ++$i}}</div>
                        </x-data-table.td>

                        <x-data-table.td>
                            <div class="text-sm text-gray-700 dark:text-gray-100 font-semibold">{{ $user->name }}</div>
                        </x-data-table.td>

                        <x-data-table.td>
                            <div class="text-sm text-gray-700 dark:text-gray-100 font-semibold">{{ $user->email }}</div>
                        </x-data-table.td>

                        <x-data-table.td>
                            @if(!empty($user->getRoleNames()))
                            <div class="grid grid-cols-4 gap-1">
                                @foreach($user->getRoleNames() as $v)
                                <label class="px-1 py-0.5 bg-green-500 rounded text-white text-xs text-center"> {{ $v }}
                                </label>
                                @endforeach
                            </div>

                            @endif
                        </x-data-table.td>

                        <x-data-table.td>
                            <x-action {{-- view="{{ route('users.show',$user->id) }}" --}}
                                edit="{{ route('users.edit', $user->id) }}"
                                delete="{{ route('users.destroy', $user->id) }}" />
                        </x-data-table.td>
                    </x-data-table.tr>
                    @empty
                    <x-data-table.empty colspan=4 />
                    @endforelse
                </x-slot>

                {!! $data->links() !!}
            </x-form.table>
        </div>
    </div>
</x-app-layout>