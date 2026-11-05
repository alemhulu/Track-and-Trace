<?php

namespace App\Models;

use App\Support\AcademicYear as AcademicYearCalendar;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * An academic year the Ministry runs, numbered in the Ethiopian calendar (e.g. 2019 E.C.).
 * Exactly one year is current at a time; screens default to it.
 */
class AcademicYear extends Model
{
    // Tables that record data against a year; a year with rows in any of them cannot be deleted
    public const USED_BY = [
        'manual_allocations' => 'Ministry allocations',
        'manual_woreda_allocations' => 'woreda allocations',
        'manual_allocation_imports' => 'allocation imports',
        'school_enrolments' => 'school enrolments',
        'students' => 'student registers',
        'teacher_guide_issues' => 'teacher guide issues',
        'verification_visits' => 'verification visits',
    ];

    protected $fillable = [
        'year',
        'starts_on',
        'ends_on',
        'is_current',
        'notes',
    ];

    protected $casts = [
        'year' => 'integer',
        'starts_on' => 'date',
        'ends_on' => 'date',
        'is_current' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saved(fn () => AcademicYearCalendar::flush());
        static::deleted(fn () => AcademicYearCalendar::flush());
    }

    public function getLabelAttribute(): string
    {
        return AcademicYearCalendar::label($this->year);
    }

    // The year with its usual dates, added when it is not set up yet
    public static function ensure(int $year, bool $current = false): self
    {
        [$startsOn, $endsOn] = AcademicYearCalendar::defaultDates($year);
        $academicYear = static::query()->firstOrCreate(['year' => $year], ['starts_on' => $startsOn, 'ends_on' => $endsOn]);
        if ($current && ! $academicYear->is_current) {
            $academicYear->makeCurrent();
        }

        return $academicYear;
    }

    // Make this the only current year
    public function makeCurrent(): void
    {
        DB::transaction(function (): void {
            static::query()->whereKeyNot($this->getKey())->where('is_current', true)->update(['is_current' => false]);
            $this->update(['is_current' => true]);
        });
    }

    /**
     * How many records each table holds for this year, only the tables that have some.
     *
     * @return array<string, int> label => count
     */
    public function usage(): array
    {
        $usage = [];
        foreach (self::USED_BY as $table => $label) {
            $count = DB::table($table)->where('academic_year', (string) $this->year)->count();
            if ($count) {
                $usage[$label] = $count;
            }
        }

        return $usage;
    }
}
