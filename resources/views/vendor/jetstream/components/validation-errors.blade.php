@if ($errors->any())
    <div {{ $attributes->merge(['class' => 'px-4 py-3 border rounded-lg bg-rose-50 border-rose-200 text-rose-800 dark:bg-rose-950/40 dark:border-rose-900 dark:text-rose-200']) }}>
        <div class="text-sm font-semibold">{{ __('Please fix the following:') }}</div>

        <ul class="mt-1.5 list-disc list-inside text-sm space-y-0.5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
