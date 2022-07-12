<div class="flex px-5 gap-x- items-center bg-lime-50 text-lime-800 pb-1 pt-4 text-sm">
    <span class="pr-1">Show</span>
    <x-action.select class="w-20 flex py-0.5" wire:model="recordes">
        <option value="5">5</option>
        <option value="10">10</option>
        <option value="25">25</option>
        <option value="50">50</option>
        <option value="100">100</option>
    </x-action.select>
    <span class="pl-1">entries</span>
</div>