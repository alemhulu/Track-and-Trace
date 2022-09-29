@props(['batch' => '#PO1091479181', 'qr' => '100001'])
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
                    <span class="text-md font-bold text-gray-400 dark:text-blue-200 leading-none">
                        {{ $batch }}</span>
                </div>

                @if ($qr)
                <div class="text-left pt-1">
                    <x-jet-label class="text-xs text-gray-400 dark:text-gray-400" value="QR Code" />
                    <span
                        class="flex justify-center p-1 text-md font-bold text-blue-50 dark:text-blue-200 leading-none tracking-wide bg-blue-500 rounded-full">
                        {{ $qr }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>