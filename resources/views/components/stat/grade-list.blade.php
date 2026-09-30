@props(['grade' => '1', 'received' => '00', 'sent' => '00', 'available' => '00', 'status' => 'Pending', 'statusType' =>
'secondary'])
@php
$statusClasses = [
'secondary' => 'bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-200',
'danger' => 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-200',
'info' => 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-200',
'warning' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-200',
'success' => 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-200',
'primary' => 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-200',
][$statusType ?? 'secondary'];
@endphp
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

                <div class="text-left pt-2">
                    <x-jet-label class="text-xs text-gray-400 dark:text-gray-200" value="Package Status" />
                    <span
                        class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold capitalize {{ $statusClasses }}">
                        {{ $status }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
