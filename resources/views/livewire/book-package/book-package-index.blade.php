<div class="bg-white dark:bg-gray-800 rounded-lg">
    <x-stat.section class="" name="Packages Information">
        <x-stat.list value="{{ $total ?? '0' }}" text="Total Packages" />
        <x-stat.list value="{{ $sent ?? '0'}}" text="Total Packages Sent" />
        <x-stat.list value="{{ $received ?? '0' }}" text="Total Packages Received" />
        <x-stat.list value="{{ $available ?? '0'}}" text="Total Packages Available" />
    </x-stat.section>


    <x-form.section class="mx-8 border-b dark:border-gray-700" title="Available Packages Per Book"
        subtitle="All Package Information Grouped By Subject For all Grades" />

    <div class="divide-y dark:divide-gray-700">


        @foreach ($subjects as $packages)

        <x-stat.section name="{{ $packages[0]['subject']['name']}}">
            @foreach ($packages as $package)
            <x-stat.grade-list grade="{{ $package['grade']['name']}}" sent="{{ $package['sent']}}"
                received="{{ $package['received']}}" available="{{ $package['balance']}}" />
            @endforeach
        </x-stat.section>
        @endforeach

    </div>
</div>