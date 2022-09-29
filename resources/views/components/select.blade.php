<select {{ $attributes->merge(['class' => 'mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300
    focus:outline-none focus:ring-indigo-500
    focus:border-indigo-500 sm:text-sm rounded-md dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400
    dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500'])}}>
    {{ $slot }}
</select>