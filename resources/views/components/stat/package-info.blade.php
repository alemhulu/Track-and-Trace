@props(['batch' => '#PO1091479181', 'quantity' => '00', 'range' => '100001 - 100050'])
<div {{ $attributes->merge([ 'class' => '']) }}>
    <div class="flex flex-col text-center border border-blue-200 dark:border-gray-400 rounded-lg">
        <div class="flex flex-row">
            <div
                class="w-16 bg-blue-100 dark:bg-blue-700 flex items-center justify-center text-2xl text-blue-700 dark:text-blue-100 rounded-l-lg ">
                <i class="text-4xl flex fi fi-rr-box p-2 font-semibold"></i>
            </div>

            <div class="flex flex-col py-1 px-3 min-w-fit">
                <div class="text-left">
                    <x-jet-label class="text-xs text-gray-400 dark:text-gray-400" value="Batch Number" />
                    <span class="text-md font-bold text-blue-600 dark:text-blue-200 leading-none">
                        {{ $batch }}</span>
                </div>

                @if ($range)
                <div class="text-left pt-1">
                    <x-jet-label class="text-xs text-gray-400 dark:text-gray-400" value="QRs Range" />
                    <span class="text-md font-bold text-blue-600 dark:text-blue-200 leading-none tracking-wide">
                        {{ $range }}</span>
                </div>
                @endif


                <div class="text-left">
                    <x-jet-label class="text-xs text-gray-400 dark:text-gray-400" value="Quantity" />
                    <span
                        class="flex items-center justify-center text-md font-bold text-blue-50 dark:text-blue-200 rounded-full bg-blue-500 lining-nums max-w-xs">
                        {{ $quantity }}</span>
                </div>
            </div>
        </div>
    </div>
</div>