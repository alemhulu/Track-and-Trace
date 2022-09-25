@props(['title', 'total' =>0, 'tabLink'=>'', 'icon', 'link'=>'#'])

<li @class(['col-span-1 flex shadow-sm hover:shadow-md rounded-lg hover:font-semibold dark:shadow-gray-600
    dark:bg-gray-800 bg-white cursor-pointer','font-bold shadow-lg bg-blue-50 dark:bg-gray-700'=>
    request()->routeIs($link.'*')])>
    <a @if ($link !='#' ) href="{{ route($link) }}" @else onclick="openTab(event,'{{ $tabLink }}')" @endif
        class="flex ">
        <button {{ $attributes ->merge(['type' => 'button', 'class' => 'flex-shrink-0 flex items-center justify-center
            w-16 h-18 text-white text-sm font-semibold rounded-l-lg uppercase']) }}>
            {{ substr($title,0, 3) }}
        </button>
    </a>

    <div class="flex-1 flex items-center justify-between rounded-r-md truncate relative">
        <a @if ($link !='#' ) href="{{ route($link) }}" @else onclick="openTab(event,'{{ $tabLink }}')" @endif
            class="absolute inset-0 h-full w-full"></a>
        <div class="flex-1 px-4 py-2 text-sm truncate">
            <a @if ($link !='#' ) href="{{ route($link) }}" @endif class="text-gray-600 dark:text-gray-300">
                {{ $title }}
            </a>
            <p class="text-gray-500 font-medium dark:text-gray-400">
                @isset($total)
                {{ $total }} {{__(' Total') }}
                @endisset
            </p>
        </div>

        <div class="flex-shrink-0 pr-2">
            <a @if ($link !='#' ) href="{{ route($link) }}" @else onclick="openTab(event,'{{ $tabLink }}')" @endif>
                <x-page.actionbtn @class(['w-8 h-8 inline-flex items-center justify-center', 'bg-blue-400 text-white'=>
                    request()->routeIs($link.'*') ])>
                    <span class="flex font-bold @isset($icon){{ $icon }}@endisset text-base dark:text-lime-600"></span>
                </x-page.actionbtn>
            </a>
        </div>
    </div>
</li>