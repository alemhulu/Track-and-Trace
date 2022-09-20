<select {{ $attributes->merge([ 'class' => 'block appearance-none w-full bg-lime-50 border
    border-lime-600 focus:border-lime-400 text-gray-700 py-1 px-4 pr-8 rounded
    leading-tight focus:bg-white focus:outline-none disabled:opacity-25 transition']) }}>

    {{ $slot }}
</select>