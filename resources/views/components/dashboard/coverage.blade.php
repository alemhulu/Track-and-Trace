@props(['overview', 'year'])
{{-- Did the books reach students? Coverage, open problems and verification for the user's area --}}
@php
    $percent = fn ($share) => $share === null ? '—' : round($share * 100) . '%';
    $tiles = [
        ['label' => 'Books with students', 'value' => $percent($overview['coverage']), 'hint' => number_format($overview['issued']) . ' of ' . number_format($overview['required']) . ' required student books', 'tone' => 'emerald', 'icon' => 'fi-rr-graduation-cap'],
        ['label' => 'Schools below ' . round(\App\Services\Oversight\OversightService::LOW_COVERAGE * 100) . '%', 'value' => number_format($overview['schools_low']), 'hint' => number_format($overview['schools_with_data']) . ' of ' . number_format($overview['schools']) . ' schools have enrolment or students recorded', 'tone' => 'gold', 'icon' => 'fi-rr-school'],
        ['label' => 'Open problems', 'value' => number_format($overview['open_discrepancies'] + $overview['open_misroutes']), 'hint' => $overview['open_discrepancies'] . ' discrepancies, ' . $overview['open_misroutes'] . ' misrouted packages', 'tone' => 'rose', 'icon' => 'fi-rr-exclamation'],
        ['label' => 'Schools verified', 'value' => number_format($overview['schools_visited']), 'hint' => $overview['holding_rate'] === null ? 'No visits yet' : 'Students showed ' . $percent($overview['holding_rate']) . ' of their recorded books', 'tone' => 'brand', 'icon' => 'fi-rr-shield-check'],
    ];
@endphp
<section>
    <div class="flex flex-wrap items-baseline justify-between gap-2 mb-3">
        <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Reaching students <span class="font-normal text-slate-400">· {{ $year }}</span></h3>
        <span class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
            Updated every 10 minutes ·
            <a href="{{ route('manual-tracking.exports', 'coverage') }}" class="btn-secondary !py-1 !px-2.5 !text-xs"><i class="flex fi fi-rr-download"></i> Export school coverage</a>
        </span>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($tiles as $tile)
        <x-kpi-card :label="$tile['label']" :value="$tile['value']" :hint="$tile['hint']" :tone="$tile['tone']" :icon="$tile['icon']" />
        @endforeach
    </div>

    @if ($overview['schools_with_data'])
    <div class="grid grid-cols-1 gap-4 mt-4 lg:grid-cols-2">
        <div class="p-5 card">
            <p class="mb-3 text-sm font-semibold text-slate-900 dark:text-slate-100">Coverage by zone</p>
            <table class="w-full text-sm">
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach ($overview['by_zone'] as $zone)
                    <tr>
                        <td class="py-1.5 pr-2 text-slate-700 dark:text-slate-200">{{ $zone['zone'] }} <span class="text-xs text-slate-500">({{ $zone['schools'] }} schools)</span></td>
                        <td class="py-1.5"><x-school.coverage-bar :value="$zone['coverage']" /></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-5 card">
            <p class="mb-3 text-sm font-semibold text-slate-900 dark:text-slate-100">Schools needing attention</p>
            <table class="w-full text-sm">
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach ($overview['lowest'] as $school)
                    <tr>
                        <td class="py-1.5 pr-2">
                            <a href="{{ route('manual-tracking.schools.show', $school['id']) }}" class="font-medium text-brand-700 hover:underline dark:text-brand-300">{{ $school['name'] }}</a>
                            <div class="text-xs text-slate-500">{{ $school['place'] }} · {{ number_format($school['shortfall']) }} short · {{ $school['visited'] ? 'visited ' . $school['visited'] : 'not visited' }}</div>
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
