<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:text-gray-200">
            {{ __('Location') }}
        </h2>
    </x-slot>

    <div class="mb-6">
        <x-page.nav col='5'>
            <x-page.nav-link class="bg-yellow-600" title="Country" tabLink="Country" icon="fi fi-rr-plus" total="0" />
            <x-page.nav-link class="bg-green-600" title="Region / City" tabLink="Region" icon="fi fi-rr-plus"
                total="0" />
            <x-page.nav-link class="bg-blue-800" title="Zone / SubCity" tabLink="Zone" icon="fi fi-rr-plus" total="0" />
            <x-page.nav-link class="bg-pink-800" title="Woreda" tabLink="Woreda" icon="fi fi-rr-plus" total="0" />
            <x-page.nav-link class="bg-blue-500" title="kebele" tabLink="Kebele" icon="fi fi-rr-plus" total="0" />
        </x-page.nav>

        <div id="Country" class="mt-6  tab mb-6" style="display:none; ">
            <livewire:location.country>
        </div>

        <div id="Region" class="mt-6  tab mb-6" style="display:none; ">
            <livewire:location.region>
        </div>

        <div id="Zone" class="mt-6  tab mb-6" style="display:none; ">
            <livewire:location.zone>
        </div>

        <div id="Woreda" class="mt-6  tab mb-6" style="display:none; ">
            <livewire:location.woreda>
        </div>

        <div id="Kebele" class="mt-6  tab mb-6" style="display:none; ">
            <livewire:location.kebele>
        </div>
    </div>
</x-app-layout>