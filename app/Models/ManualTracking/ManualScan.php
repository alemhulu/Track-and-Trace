<?php

namespace App\Models\ManualTracking;

use App\Models\Organization;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One QR scan at a warehouse, hub or school. Written only by ManualScanService.
 */
class ManualScan extends ManualBaseModel
{
    public const CONTEXT_SORT = 'sort';
    public const CONTEXT_RECEIVE = 'receive';

    public const KIND_PACKAGE = 'package';
    public const KIND_BOOK = 'book';
    public const KIND_UNKNOWN = 'unknown';

    public const FLAG_MISROUTE = 'misroute_zone';
    public const FLAG_WRONG_REGION = 'wrong_region';
    public const FLAG_UNKNOWN = 'unknown_code';
    public const FLAG_AMBIGUOUS = 'ambiguous_zone';
    public const FLAG_NOT_ON_SHIPMENT = 'not_on_shipment';

    // Flags that need someone upstream to act; the others are only shown to the scanner
    public const ALERT_FLAGS = [self::FLAG_MISROUTE, self::FLAG_WRONG_REGION];

    public const ALERT_OPEN = 'open';
    public const ALERT_RESOLVED = 'resolved';

    public const FLAG_LABELS = [
        self::FLAG_MISROUTE => 'Package for another zone',
        self::FLAG_WRONG_REGION => 'Book for another region',
        self::FLAG_UNKNOWN => 'Code not recognised',
        self::FLAG_AMBIGUOUS => 'Zone name matches several zones',
        self::FLAG_NOT_ON_SHIPMENT => 'Title not on this shipment',
    ];

    protected $casts = [
        'scanned_at' => 'datetime',
        'voided_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function distribution(): BelongsTo
    {
        return $this->belongsTo(ManualDistribution::class, 'manual_distribution_id');
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(ManualBook::class, 'manual_book_id');
    }

    public function scanner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'scanned_by');
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function scopeLive(Builder $query): Builder
    {
        return $query->whereNull('voided_at');
    }

    public function isAlert(): bool
    {
        return in_array($this->flag, self::ALERT_FLAGS, true);
    }

    /**
     * Scans made inside the actor's area, plus scans on shipments the actor can see, so
     * the office that sent a misrouted package sees the alert too.
     */
    public function scopeAccessibleBy(Builder $query, ?User $actor): Builder
    {
        if (! $actor) {
            return $query->whereRaw('1 = 0');
        }

        if ($actor->hasNationalAccess() || $actor->hasRole('Admin')) {
            return $query;
        }

        return $query->where(function (Builder $scoped) use ($actor): void {
            $scoped->whereIn('organization_id', Organization::query()->accessibleBy($actor)->select('id'))
                ->orWhereIn('manual_distribution_id', ManualDistribution::query()->accessibleBy($actor)->select('id'));
        });
    }
}
