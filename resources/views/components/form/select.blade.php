<select {{ $attributes->merge([ 'class' => 'block w-full appearance-none border-gray-300 focus:border-indigo-300
    focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm placeholder:text-gray-400
    placeholder:text-xs mt-1 apiranc']) }}>
    {{ $slot }}
</select>