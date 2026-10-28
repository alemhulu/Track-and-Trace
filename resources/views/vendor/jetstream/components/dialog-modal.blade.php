@props(['id' => null, 'maxWidth' => null])

<x-jet-modal :id="$id" :maxWidth="$maxWidth" {{ $attributes }}>
    <div class="px-6 py-4">
        <div class="text-lg font-semibold text-slate-900 dark:text-white">
            {{ $title }}
        </div>

        <div class="mt-4 text-sm text-slate-600 dark:text-slate-300">
            {{ $content }}
        </div>
    </div>

    <div class="flex flex-row justify-end gap-3 px-6 py-4 border-t bg-slate-50 border-slate-100 dark:bg-slate-800/60 dark:border-slate-800 text-right">
        {{ $footer }}
    </div>
</x-jet-modal>