@props(['value' => '00', 'text' =>'total'])
<div>
    <div class="flex flex-col px-4 py-8 text-center border border-blue-200 rounded-lg">
        <dt class=" order-last text-lg font-bold text-gray-500 dark:text-blue-300">
            {{ $text }}
        </dt>
        <dd class="text-4xl font-extrabold text-blue-600 md:text-5xl capitalize dark:text-blue-200 "> {{ $value }} </dd>
    </div>
</div>