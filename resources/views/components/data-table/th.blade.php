<th {{ $attributes ->merge(['class' => 'px-5 py-3 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase
    tracking-wider
    cursor-pointer '])
    }}>
    <div class="flex items-center">
        {{ $slot }}

        @if ($attributes->has('id'))
        <span class="pl-2 inline-flex py-0 relative">
            <div class="flex items-center ">
                <div class="absolute text-lg -bottom-5 pb-2">
                    <i class="fi fi-rr-caret-up text-lime-300 upcart"></i>
                </div>

                <div class="absolute text-lg -top-2">
                    <i class="fi fi-rr-caret-down text-lime-300 upcart"></i>
                </div>
            </div>
        </span>
        @endif

    </div>
</th>