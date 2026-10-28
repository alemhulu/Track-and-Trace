<?php

namespace App\Models\School;

use App\Models\Grade;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    public const STATUS_ACTIVE = 'active';

    public const STATUSES = [
        self::STATUS_ACTIVE => 'Active',
        'transferred' => 'Transferred',
        'dropped_out' => 'Dropped out',
    ];

    protected $fillable = [
        'organization_id',
        'academic_year',
        'student_no',
        'name',
        'sex',
        'grade_id',
        'section',
        'status',
    ];

    protected $casts = [
        'academic_year' => 'integer',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }

    public function bookIssues(): HasMany
    {
        return $this->hasMany(StudentBookIssue::class);
    }

    // Books the student holds now
    public function heldBooks(): HasMany
    {
        return $this->bookIssues()->whereNull('returned_at');
    }
}
