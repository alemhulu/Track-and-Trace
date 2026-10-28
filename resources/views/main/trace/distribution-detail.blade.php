<x-app-layout>
    <x-slot name="header">
        <h2>{{ __('Distribution Detail') }}</h2>
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
