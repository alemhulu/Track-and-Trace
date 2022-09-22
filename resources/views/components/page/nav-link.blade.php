@props(['title', 'total', 'tabLink'=>'', 'icon', 'link'=>'#'])

<li @class(['col-span-1 flex shadow-sm hover:shadow-md rounded-md hover:font-semibold dark:shadow-gray-800','font-bold
    shadow-lg'=>request()->routeIs($link)])>
    <a @if ($link !='#' ) href="{{ route($link) }}" @endif class="flex">
        <button {{ $attributes ->merge(['type' => 'button', 'class' => 'flex-shrink-0 flex items-center justify-center
            w-16 text-white text-sm font-semibold rounded-l-md uppercase']) }}>
            {{ substr($title,0, 3) }}
        </button>
    </a>

    <div class="flex-1 flex items-center justify-between bg-white rounded-r-md truncate dark:bg-gray-800">
        <div class="flex-1 px-4 py-2 text-sm truncate">
            <a @if ($link !='#' ) href="{{ route($link) }}" @endif
                class="text-gray-600 dark:text-gray-300">{{ $title }}</a>
            <p class="text-gray-500 font-medium dark:text-gray-400">
                @isset($total)
                {{ $total }} {{__(' Total') }}
                @endisset
            </p>
        </div>

        <div class="flex-shrink-0 pr-2">
            @if ($link != '#')
            <a href="{{ route($link) }}">
                <x-page.actionbtn @class(['w-8 h-8 inline-flex items-center justify-center','bg-blue-400'=>
                    request()->routeIs($link) ])>
                    <span class="flex font-bold @isset($icon){{ $icon }}@endisset text-base dark:text-lime-600 @if(
                    request()->routeIs($link) ) text-white @endif "></span>
                </x-page.actionbtn>
            </a>
            @else
            <x-page.actionbtn onclick="openTab(event,'{{ $tabLink }}')"
                class="w-8 h-8 inline-flex items-center justify-center change">
                <span class="flex font-bold @isset($icon){{ $icon }}@endisset text-base dark:text-lime-600"></span>
            </x-page.actionbtn>
            @endif
        </div>
    </div>
</li>