@props(['grade' => '1', 'received' => '00', 'sent' => '00', 'available' => '00'])
<div {{ $attributes->merge([ 'class' => '']) }}>
    <div class="flex flex-col px-4text-center border border-blue-200 rounded-lg">
        <div class="grid grid-cols-2">
            <div class="flex flex-col items-center justify-center py-3 bg-blue-50 dark:bg-blue-900 rounded-l-lg">
                <dt class="order-last text-lg font-bold text-gray-500 dark:text-blue-300">
                    Grade
                </dt>
                <dd class="text-4xl font-extrabold text-blue-600 dark:text-blue-200 md:text-5xl lg:text-7xl">
                    {{ $grade }}
                </dd>
            </div>

            <div class="flex flex-col py-3 px-3 min-w-fit">
                <div class="text-left">
                    <x-jet-label class="text-xs text-gray-400 dark:text-gray-200" value="Package Received" />
                    <span class="text-xl font-bold text-blue-600 dark:text-blue-200 leading-none tracking-wider">
                        {{ $received }}</span>
                </div>

                <div class="text-left">
                    <x-jet-label class="text-xs text-gray-400 dark:text-gray-200" value="Package Sent" />
                    <span class="text-xl font-bold text-blue-600 dark:text-blue-200 leading-none tracking-wider">
                        {{ $sent }}</span>
                </div>

                <div class="text-left">
                    <x-jet-label class="text-xs text-gray-400 dark:text-gray-200" value="Package Available" />
                    <span class="text-xl font-bold text-blue-600 dark:text-blue-200 leading-none tracking-wider">
                        {{ $available }}</span>
                </div>
            </div>
        </div>
    </div>
</div>