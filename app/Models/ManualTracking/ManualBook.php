<?php

namespace App\Models\ManualTracking;

use App\Models\Grade;
use App\Models\Region;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A book title tracked by quantity: one grade and subject printed for one region,
 * shipped in packages of student textbooks plus (optionally) one teacher guide.
 */
class ManualBook extends ManualBaseModel
{
    public const EDITION_STUDENT = 'student';
    public const EDITION_TEACHER = 'teacher';

    public const EDITIONS = [
        self::EDITION_STUDENT => 'Student Textbook',
        self::EDITION_TEACHER => 'Teacher Guide',
    ];

    protected $fillable = [
        'code',
        'print_code',
        'source_key',
        'title',
        'grade_name',
        'subject_name',
        'language',
        'region_id',
        'grade_id',
        'subject_id',
        'has_teacher_guide',
        'students_per_package',
        'isbn',
        'edition',
        'total_copies',
        'notes',
    ];

    // Mirror the column defaults so package arithmetic works before a reload
    protected $attributes = [
        'has_teacher_guide' => true,
        'students_per_package' => 40,
    ];

    protected $casts = [
        'total_copies' => 'integer',
        'has_teacher_guide' => 'boolean',
        'students_per_package' => 'integer',
    ];

    /**
     * Copies in a count of full packages plus a partial package (or loose copies), and any
     * extra teacher guides packed on their own.
     *
     * @return array{student: int, teacher: int}
     */
    public function copiesFor(int $fullPackages, int $partialBooks = 0, int $partialTeacherGuides = 0, int $extraTeacherGuides = 0): array
    {
        return [
            self::EDITION_STUDENT => $fullPackages * $this->students_per_package + $partialBooks,
            self::EDITION_TEACHER => $this->has_teacher_guide ? $fullPackages + $partialTeacherGuides + $extraTeacherGuides : 0,
        ];
    }

    /**
     * How a count of copies is normally packed: full packages with one teacher guide each, the
     * remaining student books in one partial package with one teacher guide, and any teacher
     * guides still left over packed separately as extra teacher guides.
     *
     * @return array{full_packages: int, partial_package_books: int, partial_teacher_guides: int, extra_teacher_guides: int}
     */
    public function packagesFor(int $studentBooks, int $teacherGuides = 0): array
    {
        $full = intdiv($studentBooks, $this->students_per_package);
        $partial = $studentBooks % $this->students_per_package;
        $leftOver = $this->has_teacher_guide ? max(0, $teacherGuides - $full) : 0;
        $partialGuides = $partial > 0 ? min(1, $leftOver) : 0;

        return [
            'full_packages' => $full,
            'partial_package_books' => $partial,
            'partial_teacher_guides' => $partialGuides,
            'extra_teacher_guides' => $leftOver - $partialGuides,
        ];
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function codes(): HasMany
    {
        return $this->hasMany(\App\Models\ManualTracking\ManualBookCode::class, 'manual_book_id');
    }

    public function balances(): HasMany
    {
        return $this->hasMany(\App\Models\ManualTracking\ManualStockBalance::class, 'manual_book_id');
    }

    public function packages(): HasMany
    {
        return $this->hasMany(\App\Models\ManualTracking\ManualBookPackage::class, 'manual_book_id');
    }

    public function distributionLines(): HasMany
    {
        return $this->hasMany(\App\Models\ManualTracking\ManualDistributionLine::class, 'manual_book_id');
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(\App\Models\ManualTracking\ManualAllocation::class, 'manual_book_id');
    }

    public function stockLedgers(): HasMany
    {
        return $this->hasMany(\App\Models\ManualTracking\ManualStockLedger::class, 'manual_book_id');
    }

    // Titles printed for one region, plus titles used in every region; no region keeps them all
    public function scopeForRegion(Builder $query, $regionId): Builder
    {
        return $query->when(filled($regionId), fn (Builder $books) => $books->where(
            fn (Builder $match) => $match->whereNull('manual_books.region_id')->orWhere('manual_books.region_id', $regionId)
        ));
    }

    // Titles printed for the user's region, plus titles used in every region
    public function scopeAccessibleBy(Builder $query, ?User $actor): Builder
    {
        if (! $actor) {
            return $query->whereRaw('1 = 0');
        }

        $regionId = $actor->hasRole('Admin') ? null : $actor->locationScope()['region_id'];

        return $query->when($regionId !== null, fn (Builder $books) => $books->where(
            fn (Builder $match) => $match->whereNull('region_id')->orWhere('region_id', $regionId)
        ));
    }
}
