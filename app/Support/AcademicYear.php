<?php

namespace App\Support;

use App\Models\AcademicYear as AcademicYearModel;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Schema;

/**
 * Ethiopian academic years start in Meskerem (11 September) and are numbered
 * 7-8 years behind the Gregorian calendar: September 2026 starts 2019 E.C.
 *
 * Admins manage the years on the Academic Years screen; the one they mark current is
 * the default everywhere. Without managed years it falls back to the calendar.
 */
final class AcademicYear
{
    /** @var array{current: ?int, years: array<int, int>}|null */
    private static ?array $managed = null;

    public static function current(?CarbonInterface $date = null): int
    {
        if ($date === null && ($year = self::managed()['current'])) {
            return $year;
        }

        return self::calendarYear($date);
    }

    // The academic year a date falls in by the calendar alone
    public static function calendarYear(?CarbonInterface $date = null): int
    {
        $date ??= now();
        $year = $date->year - 7;

        return $date->toDateString() >= self::defaultDates($year)[0] ? $year : $year - 1;
    }

    // e.g. "2019 E.C. (2026/27)"
    public static function label(int $year): string
    {
        $gregorianStart = $year + 7;

        return sprintf('%d E.C. (%d/%02d)', $year, $gregorianStart, ($gregorianStart + 1) % 100);
    }

    /**
     * Meskerem 1 to the day before the next Meskerem 1. Meskerem 1 falls on 12 September
     * in the year before a Gregorian leap year.
     *
     * @return array{0: string, 1: string} Y-m-d dates
     */
    public static function defaultDates(int $year): array
    {
        $newYear = fn (int $gregorian) => CarbonImmutable::create($gregorian, 9, CarbonImmutable::create($gregorian + 1)->isLeapYear() ? 12 : 11)->startOfDay();
        $start = $newYear($year + 7);

        return [$start->toDateString(), $newYear($year + 8)->subDay()->toDateString()];
    }

    /**
     * Years to pick from: the managed years (newest first), or the current year and the two
     * before it when none are set up.
     *
     * @return array<int, string>
     */
    public static function options(): array
    {
        $years = self::managed()['years'];
        if (! $years) {
            $current = self::current();
            $years = [$current, $current - 1, $current - 2];
        }

        return collect($years)->mapWithKeys(fn ($year) => [$year => self::label($year)])->all();
    }

    // A year from a request when it is one of the years to pick from, otherwise the current year
    public static function resolve($year): int
    {
        $year = (int) $year;

        return $year && self::isKnown($year) ? $year : self::current();
    }

    public static function isKnown(int $year): bool
    {
        return array_key_exists($year, self::options());
    }

    /**
     * Validation rules for a year that records are saved against: one set up on the Academic
     * Years screen.
     *
     * @return array<int, mixed>
     */
    public static function rules(): array
    {
        return ['required', 'integer', \Illuminate\Validation\Rule::in(array_keys(self::options()))];
    }

    // Forget the cached years after they change
    public static function flush(): void
    {
        self::$managed = null;
    }

    /**
     * @return array{current: ?int, years: array<int, int>}
     */
    private static function managed(): array
    {
        if (self::$managed !== null) {
            return self::$managed;
        }

        if (! Schema::hasTable('academic_years')) {
            return ['current' => null, 'years' => []];
        }

        $rows = AcademicYearModel::query()->orderByDesc('year')->get(['year', 'is_current']);

        return self::$managed = [
            'current' => $rows->firstWhere('is_current', true)?->year,
            'years' => $rows->pluck('year')->map(fn ($year) => (int) $year)->all(),
        ];
    }
}
