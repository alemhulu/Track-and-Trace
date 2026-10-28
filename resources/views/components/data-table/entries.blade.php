<caption class="px-5 pt-3 pb-2 text-left caption-top">
    <div class="flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400">
        <span>Show</span>
        <x-action.select class="!w-20" wire:model="recordes">
            <option value="5">5</option>
            <option value="10">10</option>
            <option value="25">25</option>
            <option value="50">50</option>
            <option value="100">100</option>
        </x-action.select>
        <span>entries</span>
    </div>
</caption>
