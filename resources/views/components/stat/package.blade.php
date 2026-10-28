@props(['batch' => '#PO1091479181', 'qr' => '100001', 'Qrcode'=> '', 'grade'=>'', 'subject', 'edition', 'isbn',
'volume', 'booktype'])
<div {{ $attributes->merge([ 'class' => '']) }}>
    <div class="flex flex-col text-center border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden">
        <div class="flex flex-row">

            @if ($Qrcode == '')
            <div
                class="w-24 shrink-0 bg-brand-50 dark:bg-brand-900/30 flex items-center justify-center text-brand-500 dark:text-brand-300">
                <i class="text-2xl flex fi fi-rr-box"></i>
            </div>
            @else
            <div class="flex flex-shrink-0 items-center p-1 bg-white border-r border-slate-200 dark:border-slate-700">
                <img src="{{ $Qrcode }}" alt="" srcset="" class="w-32 h-32">
            </div>
            @endif

            <div class="flex justify-between items-center w-full">
                <div class="flex flex-col py-1 px-3 min-w-fit">
                    <div class="text-left">
                        <x-jet-label class="text-xs text-gray-400 dark:text-gray-400" value="Batch Number" />
                        <span class="text-sm font-semibold text-gray-400 dark:text-blue-200 leading-none">
                            {{ $batch }}</span>
                    </div>

                    @if ($qr)
                    <div class="text-left pt-1">
                        <x-jet-label class="text-xs text-gray-400 dark:text-gray-400" value="QR Code" />
                        <span
                            class="badge-brand font-mono">
                            {{ $qr }}</span>
                    </div>
                    @endif
                </div>

                @if ($grade)
                <div class="flex flex-col py-1 px-3 min-w-fit">
                    <div class="text-left">
                        <x-jet-label class="text-xs text-gray-400 dark:text-gray-400" value="Grade" />
                        <span class="text-sm font-semibold text-gray-500 dark:text-blue-200 leading-none">
                            Grade {{ $grade ?? ''}}</span>
                    </div>

                    @if ($subject)
                    <div class="text-left pt-1">
                        <x-jet-label class="text-xs text-gray-400 dark:text-gray-400" value="Subject" />
                        <span
                            class="flex justify-start p-1 text-sm font-semibold  leading-none tracking-wide text-gray-500">
                            {{ $subject ?? '' }}</span>
                    </div>
                    @endif
                </div>



                <div class="flex flex-col py-1 px-3 min-w-fit">
                    <div class="text-left">
                        <x-jet-label class="text-xs text-gray-400 dark:text-gray-400" value="Edition" />
                        <span class="text-sm font-semibold text-gray-500 dark:text-blue-200 leading-none">
                            {{ $edition }}st Edition {{ now()->year }}</span>
                    </div>

                    @if ($volume)
                    <div class="text-left pt-1">
                        <x-jet-label class="text-xs text-gray-400 dark:text-gray-400" value="Volume" />
                        <span
                            class="flex justify-start p-1 text-sm font-semibold  leading-none tracking-wide text-gray-500">
                            {{ $volume }}</span>
                    </div>
                    @endif
                </div>

                <div class="flex flex-col py-1 px-3 min-w-fit">
                    <div class="text-left">
                        <x-jet-label class="text-xs text-gray-400 dark:text-gray-400" value="ISBN" />
                        <span class="text-sm font-semibold text-gray-500 dark:text-blue-200 leading-none">
                            {{ $isbn }}</span>
                    </div>

                    @if ($booktype)
                    <div class="text-left pt-1">
                        <x-jet-label class="text-xs text-gray-400 dark:text-gray-400" value="Book Type" />
                        <span
                            class="flex justify-start p-1 text-sm font-semibold  leading-none tracking-wide text-gray-500">
                            {{ $booktype }}</span>
                    </div>
                    @endif
                </div>
                @endif
            </div>

        </div>
    </div>
</div>