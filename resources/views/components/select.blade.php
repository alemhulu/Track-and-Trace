<select {{ $attributes->merge(['class' => 'mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300
    focus:outline-none focus:ring-indigo-500
    focus:border-indigo-500 sm:text-sm rounded-md'])}}>
    {{ $slot }}
</select>