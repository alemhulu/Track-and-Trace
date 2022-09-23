@props(['name' => 'subject', 'col'=>4])
<div {{ $attributes->merge([ 'class' => '']) }}>
    <section class="bg-white dark:bg-gray-800 rounded-lg">
        <div class="px-4 py-5 mx-auto sm:px-6 lg:px-8 mb-3">
            <p class="text-xl font-bold text-gray-800 sm:text-2xl dark:text-gray-400 capitalize">
                {{ $name }}
            </p>
            <div class="my-4">
                <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-{{ $col }}">
                    {{ $slot }}
                </dl>
            </div>
        </div>
    </section>
</div>