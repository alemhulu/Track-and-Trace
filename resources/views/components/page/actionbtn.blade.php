<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex justify-center items-center p-1 gap-2 rounded-lg
    text-slate-500 hover:text-brand-700 hover:bg-brand-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-400
    transition dark:text-slate-300 dark:hover:bg-slate-800']) }}>
    {{ $slot }}
</button>
