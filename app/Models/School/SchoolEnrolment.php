<?php

namespace App\Models\School;

use App\Models\Grade;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Students enrolled in one grade of a school for one academic year.
 */
class SchoolEnrolment extends Model
{
    public const STUDENTS_PER_SECTION = 40;

    protected $fillable = [
        'organization_id',
        'academic_year',
        'grade_id',
        'male',
        'female',
        'sections',
    ];

    protected $casts = [
        'academic_year' => 'integer',
        'male' => 'integer',
        'female' => 'integer',
        'sections' => 'integer',
    ];

    public function total(): int
    {
        return $this->male + $this->female;
    }

    // Recorded sections, otherwise one section per 40 students
    public function effectiveSections(): int
    {
        return $this->sections ?: (int) ceil($this->total() / self::STUDENTS_PER_SECTION);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }
}
