<?php

namespace App\Models\ManualTracking;

use Illuminate\Database\Eloquent\Relations\HasMany;

class ManualBook extends ManualBaseModel
{
    protected $fillable = [
        'code',
        'title',
        'grade_name',
        'subject_name',
        'isbn',
        'edition',
        'total_copies',
        'notes',
    ];

    protected $casts = [
        'total_copies' => 'integer',
    ];

    public function packages(): HasMany
    {
        return $this->hasMany(\App\Models\ManualTracking\ManualBookPackage::class, 'manual_book_id');
    }

    public function distributionLines(): HasMany
    {
        return $this->hasMany(\App\Models\ManualTracking\ManualDistributionLine::class, 'manual_book_id');
    }

    public function stockLedgers(): HasMany
    {
        return $this->hasMany(\App\Models\ManualTracking\ManualStockLedger::class, 'manual_book_id');
    }
}
