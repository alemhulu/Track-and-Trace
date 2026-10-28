<?php

namespace App\Models\School;

use App\Models\ManualTracking\ManualBook;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One copy of a title handed to one student; open until it is returned.
 */
class StudentBookIssue extends Model
{
    public const CONDITIONS = [
        'new' => 'New',
        'good' => 'Good',
        'fair' => 'Fair',
        'poor' => 'Poor',
    ];

    // Returns in these conditions cannot be issued again
    public const UNUSABLE_RETURN_CONDITIONS = [
        'damaged' => 'Damaged beyond use',
        'lost' => 'Lost',
    ];

    protected $fillable = [
        'student_id',
        'manual_book_id',
        'issued_at',
        'issued_by',
        'condition',
        'returned_at',
        'returned_by',
        'return_condition',
        'client_uuid',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'returned_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(ManualBook::class, 'manual_book_id');
    }
}
