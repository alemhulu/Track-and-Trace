<?php

namespace App\Models\Oversight;

use App\Models\ManualTracking\ManualBook;
use App\Models\School\Student;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One check in a visit: a title's shelf count ('stock') or one student's book ('student').
 */
class VerificationVisitItem extends Model
{
    public const KIND_STOCK = 'stock';
    public const KIND_STUDENT = 'student';

    protected $guarded = ['id'];

    public function book(): BelongsTo
    {
        return $this->belongsTo(ManualBook::class, 'manual_book_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function matches(): bool
    {
        return (int) $this->expected === (int) $this->found;
    }
}
