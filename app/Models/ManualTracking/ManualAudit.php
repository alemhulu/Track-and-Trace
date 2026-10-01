<?php

namespace App\Models\ManualTracking;

class ManualAudit extends ManualBaseModel
{
    protected $fillable = [
        'user_id',
        'action',
        'auditable_type',
        'auditable_id',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];
}
