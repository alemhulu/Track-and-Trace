@props(['overview', 'year'])
{{-- Did the books reach students? Coverage, open problems and verification for the user's area --}}
@php
    $percent = fn ($share) => $share === null ? '—' : round($share * 100) . '%';
    $tiles = [
        ['label' => 'Books with students', 'value' => $percent($overview['coverage']), 'hint' => number_format($overview['issued']) . ' of ' . number_format($overview['required']) . ' required student books', 'tone' => 'border-green-200 bg-green-50 dark:border-green-700 dark:bg-green-900/30'],
        ['label' => 'Schools below ' . round(\App\Services\Oversight\OversightService::LOW_COVERAGE * 100) . '%', 'value' => number_format($overview['schools_low']), 'hint' => number_format($overview['schools_with_data']) . ' of ' . number_format($overview['schools']) . ' schools have enrolment or students recorded', 'tone' => 'border-amber-200 bg-amber-50 dark:border-amber-700 dark:bg-amber-900/30'],
        ['label' => 'Open problems', 'value' => number_format($overview['open_discrepancies'] + $overview['open_misroutes']), 'hint' => $overview['open_discrepancies'] . ' discrepancies, ' . $overview['open_misroutes'] . ' misrouted packages', 'tone' => 'border-red-200 bg-red-50 dark:border-red-700 dark:bg-red-900/30'],
        ['label' => 'Schools verified', 'value' => number_format($overview['schools_visited']), 'hint' => $overview['holding_rate'] === null ? 'No visits yet' : 'Students showed ' . $percent($overview['holding_rate']) . ' of their recorded books', 'tone' => 'border-blue-200 bg-blue-50 dark:border-blue-700 dark:bg-blue-900/30'],
    ];
@endphp
<section class="px-5 pb-5">
    <div class="flex flex-wrap items-baseline justify-between gap-2 mb-3">
        <h3 class="text-base font-semibold text-gray-800 dark:text-gray-100">Reaching students · {{ $year }}</h3>
        <span class="text-xs text-gray-500 dark:text-gray-400">
            Updated every 10 minutes ·
            <a href="{{ route('manual-tracking.exports', 'coverage') }}" class="text-blue-700 hover:underline dark:text-blue-300">Export school coverage</a>
        </span>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($tiles as $tile)
        <div class="p-4 border rounded-lg {{ $tile['tone'] }}">
            <p class="text-sm font-medium text-gray-600 dark:text-gray-300">{{ $tile['label'] }}</p>
            <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $tile['value'] }}</p>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $tile['hint'] }}</p>
        </div>
        @endforeach
    </div>

    @if ($overview['schools_with_data'])
    <div class="grid grid-cols-1 gap-4 mt-4 lg:grid-cols-2">
        <div class="p-4 bg-white rounded-lg shadow-md dark:bg-gray-900">
            <p class="mb-2 text-sm font-semibold text-gray-800 dark:text-gray-100">Coverage by zone</p>
            <table class="w-full text-sm">
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach ($overview['by_zone'] as $zone)
                    <tr>
                        <td class="py-1.5 pr-2 text-gray-700 dark:text-gray-200">{{ $zone['zone'] }} <span class="text-xs text-gray-500">({{ $zone['schools'] }} schools)</span></td>
                        <td class="py-1.5"><x-school.coverage-bar :value="$zone['coverage']" /></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-4 bg-white rounded-lg shadow-md dark:bg-gray-900">
            <p class="mb-2 text-sm font-semibold text-gray-800 dark:text-gray-100">Schools needing attention</p>
            <table class="w-full text-sm">
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach ($overview['lowest'] as $school)
                    <tr>
                        <td class="py-1.5 pr-2">
                            <a href="{{ route('manual-tracking.schools.show', $school['id']) }}" class="text-blue-700 hover:underline dark:text-blue-300">{{ $school['name'] }}</a>
                            <div class="text-xs text-gray-500">{{ $school['place'] }} · {{ number_format($school['shortfall']) }} short · {{ $school['visited'] ? 'visited ' . $school['visited'] : 'not visited' }}</div>
                        </td>
                        <td class="py-1.5"><x-school.coverage-bar :value="$school['coverage']" /></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</section>
