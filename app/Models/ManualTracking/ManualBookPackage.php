<?php

namespace App\Models\ManualTracking;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Cartons of one title: entered by hand, or imported from the printer's package list as the
 * cartons of one carton type and content packed for a zone, all carrying the zone's package QR.
 */
class ManualBookPackage extends ManualBaseModel
{
    protected $fillable = [
        'manual_book_id',
        'academic_year',
        'region_id',
        'zone_id',
        'package_code',
        'package_qr_code',
        'package_qr_text',
        'carton_type',
        'no_of_packages',
        'books_per_package',
        'teacher_guides_per_package',
        'total_books',
        'total_teacher_guides',
        'current_balance',
        'status',
        'notes',
        'source_key',
    ];

    protected $casts = [
        'no_of_packages' => 'integer',
        'books_per_package' => 'integer',
        'teacher_guides_per_package' => 'integer',
        'total_books' => 'integer',
        'total_teacher_guides' => 'integer',
        'current_balance' => 'integer',
    ];

    public function region(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Region::class);
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Zone::class);
    }

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
