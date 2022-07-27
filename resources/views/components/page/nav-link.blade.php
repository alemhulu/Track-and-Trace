@props(['title', 'total', 'tabLink', 'icon'])
<li class="col-span-1 flex shadow-sm hover:shadow-md rounded-md hover:font-semibold ">
    <div {{ $attributes ->merge(['type' => 'button', 'class' => 'flex-shrink-0 flex items-center justify-center w-16
        text-white text-sm font-semibold rounded-l-md uppercase']) }}>
        {{ substr($title,0, 3) }}
    </div>
    <div class="flex-1 flex items-center justify-between bg-white rounded-r-md truncate">
        <div class="flex-1 px-4 py-2 text-sm truncate">
            <a href="#" class="text-gray-600">{{ $title }}</a>
            <p class="text-gray-500 font-medium">
                @isset($total)
                {{ $total }} {{__(' Total') }}
                @endisset
            </p>
        </div>
        <div class="flex-shrink-0 pr-2">
            <x-page.actionbtn onclick="openTab(event,'{{ $tabLink }}')"
                class="w-8 h-8 inline-flex items-center justify-center change">
                <span class="flex font-bold @isset($icon){{ $icon }}@endisset text-base"></span>
            </x-page.actionbtn>
        </div>
    </div>
</li>