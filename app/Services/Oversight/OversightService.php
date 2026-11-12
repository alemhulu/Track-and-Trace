<?php

namespace App\Services\Oversight;

use App\Models\ManualTracking\ManualDiscrepancy;
use App\Models\ManualTracking\ManualScan;
use App\Models\Organization;
use App\Models\Oversight\VerificationVisit;
use App\Models\School\SchoolEnrolment;
use App\Models\School\Student;
use App\Models\User;
use App\Services\School\SchoolAllocationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Whether books reached students across the actor's area: coverage per school and zone,
 * open problems, and what verification visits found.
 */
class OversightService
{
    public const LOW_COVERAGE = 0.8;

    public function __construct(private SchoolAllocationService $allocations)
    {
    }

    public function schools(User $actor): Builder
    {
        return Organization::query()
            ->accessibleBy($actor)
            ->whereHas('organizationType', fn ($type) => $type->where('name', 'School'));
    }

    /**
     * Coverage summary per school, with the school's location and latest visit.
     *
     * @return Collection<int, array{school: Organization, totals: array, visit: ?VerificationVisit}>
     */
    public function schoolRows(User $actor, int $year, ?Builder $schools = null): Collection
    {
        $schools = ($schools ?? $this->schools($actor))
            ->with(['region:id,name', 'zone:id,name', 'woreda:id,name'])
            ->orderBy('name')
            ->get();

        $summaries = $this->allocations->summaries($schools, $year);
        $visits = VerificationVisit::query()
            ->whereIn('organization_id', $schools->pluck('id'))
            ->where('academic_year', $year)
            ->latest('visited_at')
            ->get()
            ->unique('organization_id')
            ->keyBy('organization_id');

        return $schools->map(fn ($school) => [
            'school' => $school,
            'totals' => $summaries[$school->id],
            'visit' => $visits[$school->id] ?? null,
        ]);
    }

    /**
     * Schools that have enrolment or registered students for the year: only they have books to
     * cover, so only they are loaded for the dashboard (a national user can reach 50,000 schools).
     */
    public function schoolsWithFigures(User $actor, int $year): Builder
    {
        return $this->schools($actor)->where(fn (Builder $school) => $school
            ->whereIn('organizations.id', SchoolEnrolment::query()->select('organization_id')->where('academic_year', $year))
            ->orWhereIn('organizations.id', Student::query()->active()->select('organization_id')->where('academic_year', $year)));
    }

    /**
     * Dashboard figures, kept for ten minutes per user since they cover every school in the area.
     */
    public function overview(User $actor, int $year): array
    {
        return Cache::remember("oversight-overview:{$actor->id}:{$year}", now()->addMinutes(10), function () use ($actor, $year): array {
            $rows = $this->schoolRows($actor, $year, $this->schoolsWithFigures($actor, $year));
            $withData = $rows->filter(fn ($row) => $row['totals']['required_student'] > 0);
            $required = (int) $withData->sum(fn ($row) => $row['totals']['required_student']);
            $issued = (int) $withData->sum(fn ($row) => $row['totals']['issued_student']);
            $visits = VerificationVisit::query()->accessibleBy($actor)->where('academic_year', $year)->get();
            $expected = (int) $visits->sum('books_expected');

            return [
                'schools' => $this->schools($actor)->count(),
                'schools_with_data' => $withData->count(),
                'required' => $required,
                'issued' => $issued,
                'shortfall' => (int) $withData->sum(fn ($row) => $row['totals']['shortfall_student']),
                'coverage' => $required ? min(1, $issued / $required) : null,
                'schools_low' => $withData->filter(fn ($row) => $row['totals']['coverage'] < self::LOW_COVERAGE)->count(),
                'open_discrepancies' => ManualDiscrepancy::query()->accessibleBy($actor)->where('status', ManualDiscrepancy::STATUS_OPEN)->count(),
                'open_misroutes' => ManualScan::query()->accessibleBy($actor)->live()->where('alert_status', ManualScan::ALERT_OPEN)->count(),
                'visits' => $visits->count(),
                'schools_visited' => $visits->pluck('organization_id')->unique()->count(),
                'holding_rate' => $expected ? $visits->sum('books_seen') / $expected : null,
                'by_zone' => $withData
                    ->groupBy(fn ($row) => $row['school']->zone->name ?? 'No zone')
                    ->map(function ($zoneRows, $zone) {
                        $zoneRequired = (int) $zoneRows->sum(fn ($row) => $row['totals']['required_student']);
                        $zoneIssued = (int) $zoneRows->sum(fn ($row) => $row['totals']['issued_student']);

                        return ['zone' => $zone, 'schools' => $zoneRows->count(), 'required' => $zoneRequired, 'issued' => $zoneIssued, 'coverage' => $zoneRequired ? min(1, $zoneIssued / $zoneRequired) : null];
                    })
                    ->sortBy('coverage')
                    ->values()
                    ->all(),
                'lowest' => $withData
                    ->sortBy(fn ($row) => $row['totals']['coverage'])
                    ->take(8)
                    ->map(fn ($row) => [
                        'id' => $row['school']->id,
                        'name' => $row['school']->name,
                        'place' => trim(($row['school']->woreda->name ?? '') . ', ' . ($row['school']->zone->name ?? ''), ', '),
                        'coverage' => $row['totals']['coverage'],
                        'shortfall' => $row['totals']['shortfall_student'],
                        'visited' => $row['visit']?->visited_at?->toDateString(),
                    ])
                    ->values()
                    ->all(),
            ];
        });
    }
}
