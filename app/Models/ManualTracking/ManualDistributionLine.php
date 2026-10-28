<?php

namespace App\Models\ManualTracking;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * One title in a shipment. `quantity` and `teacher_guides` are the copies dispatched;
 * the received_* and damaged_* columns are filled in by the receiver.
 */
class ManualDistributionLine extends ManualBaseModel
{
    protected $fillable = [
        'manual_distribution_id',
        'manual_book_id',
        'manual_book_package_id',
        'full_packages',
        'partial_package_books',
        'partial_teacher_guides',
        'quantity',
        'teacher_guides',
        'received_quantity',
        'received_teacher_guides',
        'damaged_quantity',
        'damaged_teacher_guides',
        'source_balance_before',
        'source_balance_after',
    ];

    protected $casts = [
        'full_packages' => 'integer',
        'partial_package_books' => 'integer',
        'partial_teacher_guides' => 'integer',
        'quantity' => 'integer',
        'teacher_guides' => 'integer',
        'received_quantity' => 'integer',
        'received_teacher_guides' => 'integer',
        'damaged_quantity' => 'integer',
        'damaged_teacher_guides' => 'integer',
        'source_balance_before' => 'integer',
        'source_balance_after' => 'integer',
    ];

    /**
     * Copies dispatched per edition.
     *
     * @return array{student: int, teacher: int}
     */
    public function sentCopies(): array
    {
        return [
            ManualBook::EDITION_STUDENT => (int) $this->quantity,
            ManualBook::EDITION_TEACHER => (int) $this->teacher_guides,
        ];
    }

    public function distribution(): BelongsTo
    {
        return $this->belongsTo(\App\Models\ManualTracking\ManualDistribution::class, 'manual_distribution_id');
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(\App\Models\ManualTracking\ManualBook::class, 'manual_book_id');
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(\App\Models\ManualTracking\ManualBookPackage::class, 'manual_book_package_id');
    }

    public function discrepancies(): HasMany
    {
        return $this->hasMany(ManualDiscrepancy::class, 'manual_distribution_line_id');
    }
}
