<?php

namespace App\Models\ManualTracking;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ManualDistributionLine extends ManualBaseModel
{
    protected $fillable = [
        'manual_distribution_id',
        'manual_book_id',
        'manual_book_package_id',
        'quantity',
        'source_balance_before',
        'source_balance_after',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'source_balance_before' => 'integer',
        'source_balance_after' => 'integer',
    ];

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
}
