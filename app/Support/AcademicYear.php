<?php

namespace App\Support;

use Carbon\CarbonInterface;

/**
 * Ethiopian academic years start in Meskerem (11 September) and are numbered
 * 7-8 years behind the Gregorian calendar: September 2026 starts 2019 E.C.
 */
final class AcademicYear
{
    public static function current(?CarbonInterface $date = null): int
    {
        $date ??= now();
        $startsNewYear = $date->month > 9 || ($date->month === 9 && $date->day >= 11);

        return ($startsNewYear ? $date->year : $date->year - 1) - 7;
    }

    // e.g. "2019 E.C. (2026/27)"
    public static function label(int $year): string
    {
        $gregorianStart = $year + 7;

        return sprintf('%d E.C. (%d/%02d)', $year, $gregorianStart, ($gregorianStart + 1) % 100);
    }

    /**
     * @return array<int, string> the current year and the two before it
     */
    public static function options(): array
    {
        $current = self::current();

        return collect([$current, $current - 1, $current - 2])
            ->mapWithKeys(fn ($year) => [$year => self::label($year)])
            ->all();
    }
}
