<?php

namespace App\Models\ManualTracking;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A difference between what was dispatched and what the receiver counted.
 * Stays open until someone other than the sender and receiver resolves it.
 */
class ManualDiscrepancy extends ManualBaseModel
{
    public const TYPE_MISSING = 'missing';
    public const TYPE_DAMAGED = 'damaged';
    public const TYPE_EXCESS = 'excess';

    public const STATUS_OPEN = 'open';
    public const STATUS_RESOLVED = 'resolved';

    // Allowed resolutions per type
    public const RESOLUTIONS = [
        self::TYPE_MISSING => ['found' => 'Found (add to receiver stock)', 'written_off' => 'Written off as lost'],
        self::TYPE_DAMAGED => ['written_off' => 'Written off as unusable', 'usable' => 'Usable (add to receiver stock)'],
        self::TYPE_EXCESS => ['corrected' => 'Sender count corrected (remove from sender stock)', 'accepted' => 'Accepted as is'],
    ];

    protected $fillable = [
        'manual_distribution_line_id',
        'edition',
        'type',
        'quantity',
        'status',
        'resolution',
        'resolution_notes',
        'raised_by',
        'resolved_by',
        'resolved_at',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'resolved_at' => 'datetime',
    ];

    public function line(): BelongsTo
    {
        return $this->belongsTo(ManualDistributionLine::class, 'manual_distribution_line_id');
    }

    public function raisedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'raised_by');
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function scopeAccessibleBy(Builder $query, ?User $actor): Builder
    {
        return $query->whereHas('line.distribution', fn (Builder $shipment) => $shipment->accessibleBy($actor));
    }
}
