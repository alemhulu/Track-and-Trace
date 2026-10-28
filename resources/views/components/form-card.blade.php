@props(['title', 'subTitle', 'action', 'enctype' => 'multipart/form-data', 'method' => 'POST', 'publish'])
<div class="overflow-hidden card">
    <form enctype="{{ $enctype }}" action="{{ $action }}" method="POST">
        @csrf
        @method($method)
        <x-form.header>
            {{ $title ?? 'form'}}
        </x-form.header>

        <div class="p-5 sm:p-6">
            {{ $body }}
        </div>

        <div class="flex justify-end gap-3 px-5 py-4 border-t sm:px-6 bg-slate-50/70 border-slate-100 dark:bg-slate-800/40 dark:border-slate-800">
            <x-button type="button" btnType="secondary" class="!w-auto" onclick="history.back()">
                Cancel
            </x-button>
            <x-button type="submit" class="!w-auto">
                Save
            </x-button>
        </div>
    </form>
</div>
