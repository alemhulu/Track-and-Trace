@props(['function'=>'', 'title' ,'buttons'=> true, 'submitLabel' => 'Add', 'can' => null])
{{-- `can` hides the whole form from users without that permission ("a|b" for any of several). --}}
@if (! $can || auth()->user()?->canAny(explode('|', $can)))
<form wire:submit.prevent={{ $function }} class="h-auto">
    <div class="relative mb-6 lg:col-span-8 card">
        <x-form.header>
            {{ __($title) }}
        </x-form.header>

        <div class="p-5 sm:p-6">
            <div class="space-y-5">
                {{$slot }}
            </div>
        </div>

        @if ($buttons)
        <div class="flex justify-end gap-3 px-5 py-4 border-t sm:px-6 bg-slate-50/70 border-slate-100 dark:bg-slate-800/40 dark:border-slate-800 rounded-b-xl">
            <x-jet-secondary-button onclick="openTab(event,'non')">
                {{__('Cancel') }}
            </x-jet-secondary-button>

            <x-jet-button type="submit" class="disabled:opacity-40">
                {{ __($submitLabel) }}
                <x-action.button-loader wire:loading wire:target='{{ $function }}' />
            </x-jet-button>
        </div>
        @endif
    </div>
</form>
@endif
