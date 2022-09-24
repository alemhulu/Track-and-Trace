@props(['is_checked' => 0])
<div class="flex items-center gap-x-2 dark:text-gray-400" x-data="{ toggle: {{ $is_checked}} }">
    <button id="toggler" x-on:click="toggle === 0 ? toggle = 1  : toggle = 0" type="button"
        class="bg-gray-200 relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200"
        :class="toggle && 'bg-blue-600'" role="switch" aria-checked="0">
        <span class="sr-only">Use setting</span>
        <span aria-hidden="true"
            class="pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200"
            :class="toggle && 'translate-x-5'"></span>
    </button>

    <label x-text="toggle === 1 ? 'checked' : 'check'"> </label>
    <input type="hidden" name="is_checked" class="hidden" x-model.number="toggle" />
</div>