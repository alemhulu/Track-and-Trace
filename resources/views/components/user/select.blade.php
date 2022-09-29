@props(['users', 'select', 'hide'=>0, 'user'=>null])
<div class="">
    {{-- <x-label for="username" value="{{ $value }}" /> --}}
    <div class="flex rounded-md shadow-sm relative">
        <x-jet-input type="search" wire:model="select" placeholder="Search ID, Name or Phone"
            class="flex-1 max-w-md min-w-0 rounded-md  pl-10 " autocomplete="off" />
        <div class="absolute left-1 top-2 inline-flex items-center p-2">
            <i class="fi fi-rr-search text-gray-500 flex text-xl"></i>
        </div>

        @if (strlen($select) >1 && strlen($select)< 11) <ul
            class="divide-y divide-gray-100 block absolute top-14 max-w-md bg-white dark:bg-gray-800 border border-lime-400 rounded-lg shadow-xl overflow-y-auto max-h-[500px] overflow-x-hidden">
            {{-- <x-cardloader /> --}}
            @forelse($users as $user)
            <li wire:target='select' wire:click="setUserId({{$user->id}})">
                <div class="py-2 flex hover:bg-gray-100 dark:hover:bg-gray-600 px-4 cursor-pointer 
                @if ($loop->first) rounded-t-lg @endif @if ($loop->last) rounded-b-lg @endif w-96">
                    <img class="h-8 w-8 rounded-full object-cover" src="{{ $user->profile_photo_url }}"
                        alt="{{ $user->id }}">

                    <div class="mx-3">
                        <p class="text-sm font-bold text-gray-500 dark:text-gray-300">
                            {{ $user->name }}
                        </p>

                        <p class="text-gray-500 dark:text-gray-300 text-xs flex pt-0.5 justify-between">
                            <span class="mr-2 flex items-center gap-x-1">
                                <i class="fi fi-rr-id-badge flex"></i>
                                {{ $user->email }}
                            </span>

                            <span class="flex items-center gap-x-1">
                                @if ($user->phone)
                                <i class="fi fi-rr-address-book flex "></i>
                                {{ $user->phone }}
                                @endif
                            </span>
                        </p>
                    </div>
                </div>
            </li>
            @empty
            <li class="py-1.5 flex hover:bg-lime-100  px-4 cursor-pointer  rounded-lg">
                <div class="ml-3">
                    <p class="text-xs font-bold text-gray-600">No results Found for
                    </p>
                    <p class="text-gray-500 text-xs flex pt-0.5">
                        <span class="ml-2">"{{ $select }}"</span>
                    </p>
                </div>
            </li>
            @endforelse
            </ul>
            @endif
    </div>

    <div class="mt-3">
        @if ($hide==1)
        <x-label for="username" value="Assigned User Info" />
        <div class="mt-1 flex rounded-md relative">
            <img class=" h-11 w-11 rounded-full shadow-sm object-cover" src="{{ $user->profile_photo_url }}" alt="img">

            <div class="mx-3">
                <p class="text-sm font-bold text-gray-500 dark:text-gray-300">
                    {{ $user->name }}
                </p>

                <p class="text-gray-500 dark:text-gray-300 text-xs flex pt-0.5 justify-between">
                    <span class="mr-2 flex items-center gap-x-1">
                        <i class="fi fi-rr-id-badge flex"></i>
                        {{ $user->email }}
                    </span>

                    <span class="flex items-center gap-x-1">
                        @if ($user->phone)
                        <i class="fi fi-rr-address-book flex "></i>
                        {{ $user->phone }}
                        @endif
                    </span>
                </p>
            </div>
            <span class="flex items-center float-right transition-all delay-150 animate-pulse">
                <i
                    class="fi fi-rr-check text-xl text-lime-50 ml-10 flex shadow rounded-full p-2 bg-blue-400 w-10 h-10 items-center justify-center"></i>
            </span>
        </div>
        @endif
    </div>
    <x-jet-input-error for="assigned_user_id" alert="select Value" />
</div>