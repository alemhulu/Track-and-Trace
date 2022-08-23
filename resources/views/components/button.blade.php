@props(['btnType'])
@php
$btnType = [
'secondary' => 'inline-flex items-center px-4 py-2 bg-gray-200 border
border-transparent rounded-md font-semibold text-xs text-gray-500 hover:text-gray-600 capitalize tracking-widest
hover:bg-gray-300
active:bg-gray-300 focus:outline-none focus:border-gray-400 focus:ring ring-gray-300 disabled:opacity-25
transition
ease-in-out duration-150',
'danger' => 'inline-flex items-center px-4 py-2 bg-red-500 border
border-transparent rounded-md font-semibold text-xs text-white capitalize tracking-widest hover:bg-red-600
active:bg-red-700 focus:outline-none focus:border-red-700 focus:ring ring-red-300 disabled:opacity-25 transition
ease-in-out duration-150',
'info' => 'inline-flex items-center px-4 py-2 bg-blue-300 border
border-transparent rounded-md font-semibold text-xs text-white capitalize tracking-widest hover:bg-blue-400
active:bg-blue-500 focus:outline-none focus:border-blue-500 focus:ring ring-blue-300 disabled:opacity-25 transition
ease-in-out duration-150',
'warning' => 'inline-flex items-center px-4 py-2 bg-yellow-500 border
border-transparent rounded-md font-semibold text-xs text-white capitalize tracking-widest hover:bg-yellow-600
active:bg-yellow-600 focus:outline-none focus:border-yellow-700 focus:ring ring-yellow-300 disabled:opacity-25
transition
ease-in-out duration-150',
'success' => 'inline-flex items-center px-4 py-2 bg-green-500 border
border-transparent rounded-md font-semibold text-xs text-white capitalize tracking-widest hover:bg-green-600
active:bg-green-600 focus:outline-none focus:border-green-700 focus:ring ring-green-300 disabled:opacity-25
transition
ease-in-out duration-150',
'primary' => 'inline-flex items-center px-4 py-2 bg-blue-800 border
border-transparent rounded-md font-semibold text-xs text-white capitalize tracking-widest hover:bg-blue-700
active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition
ease-in-out duration-150',
][$btnType ?? 'primary'
];

@endphp

<button {{ $attributes->merge(['class' => $btnType]) }}>
    {{ $slot }}
</button>