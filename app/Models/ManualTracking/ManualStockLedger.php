<?php

namespace App\Models\ManualTracking;

class ManualStockLedger extends ManualBaseModel
{
    protected $fillable = [
        'manual_book_id',
        'manual_book_package_id',
        'edition',
        'movement_type',
        'movement_qty',
        'balance_after',
        'ref_type',
        'ref_id',
        'manual_distribution_line_id',
        'acted_by',
        'organization_id',
        'country_id',
        'region_id',
        'zone_id',
        'woreda_id',
        'notes',
        'client_uuid',
        'occurred_at',
    ];

    protected $casts = [
        'movement_qty' => 'integer',
        'balance_after' => 'integer',
        'occurred_at' => 'datetime',
    ];
}
