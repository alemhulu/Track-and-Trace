<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:text-gray-200">
            {{ __('Distribution Detail') }}
        </h2>
    </x-slot>

    <div class="">
        <div class="w-auto">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <x-form.card function="noop" title="Trace Book Distribution" :buttons=false>
                    <div class="space-y-8 sm:space-y-5">
                        <x-trace.distribution-detail :distribution="$distribution" />
                        <x-trace.distribution-steps-detail :steps="$distribution->steps" />
                    </div>
                </x-form.card>
            </div>
        </div>
    </div>
</x-app-layout>
