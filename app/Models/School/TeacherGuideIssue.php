<?php

namespace App\Models\School;

use App\Models\ManualTracking\ManualBook;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherGuideIssue extends Model
{
    protected $fillable = [
        'organization_id',
        'academic_year',
        'manual_book_id',
        'teacher_name',
        'section',
        'quantity',
        'issued_at',
        'issued_by',
        'returned_at',
        'returned_by',
    ];

    protected $casts = [
        'academic_year' => 'integer',
        'quantity' => 'integer',
        'issued_at' => 'datetime',
        'returned_at' => 'datetime',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(ManualBook::class, 'manual_book_id');
    }
}
