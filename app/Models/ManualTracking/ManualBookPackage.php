<?php

namespace App\Models\ManualTracking;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ManualBookPackage extends ManualBaseModel
{
    protected $fillable = [
        'manual_book_id',
        'package_code',
        'no_of_packages',
        'books_per_package',
        'total_books',
        'current_balance',
        'status',
        'notes',
    ];

    protected $casts = [
        'no_of_packages' => 'integer',
        'books_per_package' => 'integer',
        'total_books' => 'integer',
        'current_balance' => 'integer',
    ];

    public function book(): BelongsTo
    {
        return $this->belongsTo(\App\Models\ManualTracking\ManualBook::class, 'manual_book_id');
    }

    public function distributionLines(): HasMany
    {
        return $this->hasMany(\App\Models\ManualTracking\ManualDistributionLine::class, 'manual_book_package_id');
    }

    public function stockLedgers(): HasMany
    {
        return $this->hasMany(\App\Models\ManualTracking\ManualStockLedger::class, 'manual_book_package_id');
    }
}
