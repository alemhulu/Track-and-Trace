<button {{ $attributes ->merge(['type' => 'button', 'class' => 'flex justify-center items-center p-1 transition
    delay-75 ease-out transform hover:-translate-y-0.5 hover:scale-105 gap-2 border rounded-lg hover:text-lime-900
    border-transparent border-lime-200
    active:bg-lime-600 active:hover:scale-110 focus:scale-105 focus:-translate-y-0.5 focus:text-lime-900
    focus:bg-lime-200']) }}>
    {{ $slot }}
</button>