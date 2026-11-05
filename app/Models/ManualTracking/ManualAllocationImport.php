<?php

namespace App\Models\ManualTracking;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * One upload of the Ministry's allocation workbook, kept with its per-region totals and
 * the warnings raised while reading it.
 */
class ManualAllocationImport extends ManualBaseModel
{
    protected $casts = [
        'line_count' => 'integer',
        'title_count' => 'integer',
        'total_quantity' => 'integer',
        'region_totals' => 'array',
        'warnings' => 'array',
    ];

    public function importer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'imported_by');
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(ManualAllocation::class, 'manual_allocation_import_id');
    }

    public function woredaAllocations(): HasMany
    {
        return $this->hasMany(ManualWoredaAllocation::class, 'manual_allocation_import_id');
    }
}
