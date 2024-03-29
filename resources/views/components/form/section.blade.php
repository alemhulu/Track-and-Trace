@props(['title' => 'Title', 'subtitle' => 'Subtitle'])
<div {{ $attributes->merge([ 'class' => '']) }}>
    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-50">
        {{ $title }}
    </h3>
    <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
        {{ $subtitle }}
    </p>
</div>